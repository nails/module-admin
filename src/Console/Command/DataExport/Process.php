<?php

namespace Nails\Admin\Console\Command\DataExport;

use Cron\CronExpression;
use DateTime;
use Nails\Admin\Admin\Controller\Utilities;
use Nails\Admin\Constants;
use Nails\Admin\Exception\DataExport\ScheduleException;
use Nails\Admin\Factory\Email\DataExport\Fail;
use Nails\Admin\Factory\Email\DataExport\Success;
use Nails\Admin\Model\Export;
use Nails\Admin\Service\DataExport;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Console\Command\Base;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Process
 *
 * @package Nails\Admin\Console\Command\DataExport
 */
class Process extends Base
{
    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('admin:dataexport:process')
            ->setDescription('Processes any pending or scheduled data export requests');
    }

    // --------------------------------------------------------------------------

    /**
     * Executes the app
     *
     * @param InputInterface  $oInput  The Input Interface provided by Symfony
     * @param OutputInterface $oOutput The Output Interface provided by Symfony
     *
     * @return int
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput): int
    {
        parent::execute($oInput, $oOutput);

        // --------------------------------------------------------------------------

        try {

            $this->banner('Data Export: Process');
            $this
                ->enqueueScheduled()
                ->process();

        } catch (\Exception $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                [$e->getMessage()]
            );
        }

        // --------------------------------------------------------------------------

        //  Cleaning up
        $oOutput->writeln('');
        $oOutput->writeln('<comment>Cleaning up</comment>...');

        // --------------------------------------------------------------------------

        //  And we're done
        $oOutput->writeln('');
        $oOutput->writeln('Complete!');

        return self::EXIT_CODE_SUCCESS;
    }

    // --------------------------------------------------------------------------

    protected function enqueueScheduled(): self
    {
        $this->oOutput->writeln('<comment>Enqueuing scheduled exports</comment>');

        /** @var DataExport $service */
        $service = Factory::service('DataExport', Constants::MODULE_SLUG);
        /** @var DateTime $now */
        $now = Factory::factory('DateTime');
        /** @var Export $exportModel */
        $exportModel = Factory::model('Export', Constants::MODULE_SLUG);

        $numEnqueued = 0;

        foreach ($service->getAllScheduled() as $scheduled) {

            $cronExpression = $scheduled->getCronExpression();
            $source         = $service->getSourceBySlug($scheduled->getSource());
            $format         = $service->getFormatBySlug($scheduled->getFormat());
            $options        = $scheduled->getOptions();
            $users          = $scheduled->getUsers();

            try {

                if (empty($cronExpression)) {
                    throw new ScheduleException('Cron expression is empty');
                }

                $expression = new CronExpression($cronExpression);
                if (!$expression->isDue($now)) {
                    continue;

                }

                $this->oOutput->writeln(
                    sprintf(
                        'Scheduled export <info>%s</info> is due to run',
                        $scheduled::class
                    )
                );

                if (empty($source)) {
                    throw new ScheduleException(sprintf(
                        '"%s" is not a valid DataExport source',
                        $scheduled->getSource()
                    ));

                } elseif (empty($format)) {
                    throw new ScheduleException(sprintf(
                        '"%s" is not a valid DataExport format',
                        $scheduled->getFormat()
                    ));

                } elseif (empty($users)) {
                    throw new ScheduleException('No users to receive the report');
                }

                $this->oOutput->writeln(sprintf('↳ Source:  <info>%s</info>', $source->slug));
                $this->oOutput->writeln(sprintf('↳ Format:  <info>%s</info>', $format->slug));
                $this->oOutput->writeln(sprintf('↳ Options: <info>%s</info>', json_encode($options)));
                $this->oOutput->writeln(sprintf('↳ Users  : <info>%s</info>', json_encode(array_column($users, 'id'))));

                foreach ($users as $user) {
                    $result = $exportModel->create([
                        'user_id' => $user->id,
                        'method'  => $exportModel::METHOD_SCHEDULE,
                        'source'  => $source->slug,
                        'options' => json_encode($options),
                        'format'  => $format->slug,
                        'expires' => (clone $now)->add(new \DateInterval('PT' . $scheduled->getTTL() . 'S'))->format('Y-m-d H:i:s'),
                    ]);

                    $this->oOutput->writeln(
                        $result
                            ? sprintf('↳ Queued successfully for user <info>#%s</info>', $user->id)
                            : sprintf('↳ <error>Failed to queue for user #%s, %s</error>', $user->id, $exportModel->lastError())
                    );

                    if ($result) {
                        $numEnqueued++;
                    }
                }

            } catch (\Throwable $e) {
                $this->oOutput->writeln(
                    sprintf(
                        '↳ <error>Error: %s</error>',
                        $e->getMessage()
                    )
                );
            }
        }

        $this->oOutput->writeln(sprintf('Queued <info>%s</info> exports', $numEnqueued));
        $this->oOutput->writeln('');

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @throws FactoryException
     * @throws ModelException
     */
    protected function process(): self
    {

        $this->oOutput->writeln('<comment>Generating exports</comment>');

        /** @var DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        setAppSetting('data-export-cron-last-run', Constants::MODULE_SLUG, $oNow->format('Y-m-d H:i:s'));

        /** @var DataExport $oService */
        $oService = Factory::service('DataExport', Constants::MODULE_SLUG);
        /** @var Export $oModel */
        $oModel = Factory::model('Export', Constants::MODULE_SLUG);
        /** @var \Nails\Admin\Resource\Export[] $aRequests */
        $aRequests = $oModel->getAll(['where' => [['status', $oModel::STATUS_PENDING]]]);

        if (!empty($aRequests)) {

            Factory::helper('inflector');
            $this->oOutput->writeln('Processing ' . count($aRequests) . ' ' . pluralise(count($aRequests), 'request'));
            $this->oOutput->writeln('Marking as <info>RUNNING</info>');
            $oModel->setBatchStatus($aRequests, $oModel::STATUS_RUNNING);

            //  Group identical requests
            $aGroupedRequests = [];
            foreach ($aRequests as $oRequest) {
                $aHash = [$oRequest->source, $oRequest->format, $oRequest->options];
                $sHash = md5(json_encode($aHash));
                if (array_key_exists($sHash, $aGroupedRequests)) {
                    $aGroupedRequests[$sHash]->recipients[] = $oRequest->user_id;
                    $aGroupedRequests[$sHash]->ids[]        = $oRequest->id;
                } else {
                    $aGroupedRequests[$sHash] = (object) [
                        'source'     => $oRequest->source,
                        'format'     => $oRequest->format,
                        'options'    => json_decode($oRequest->options, JSON_OBJECT_AS_ARRAY),
                        'recipients' => [$oRequest->user_id],
                        'ids'        => [$oRequest->id],
                    ];
                }
            }

            /** @var Success $oSuccessEmail */
            $oSuccessEmail = Factory::factory('EmailDataExportSuccess', Constants::MODULE_SLUG);
            /** @var Fail $oFailEmail */
            $oFailEmail = Factory::factory('EmailDataExportFail', Constants::MODULE_SLUG);

            /** @var Success|Fail $email */
            foreach ([$oSuccessEmail, $oFailEmail] as $email) {
                $email->data([
                    'login_url' => Utilities::url('export'),
                ]);
            }

            foreach ($aGroupedRequests as $oRequest) {
                try {

                    $this->oOutput->writeln(
                        'Starting <info>' . $oRequest->source . '->' . $oRequest->format . '</info> (<info>' . json_encode($oRequest->options) . '</info>)'
                    );
                    $oModel->setBatchDownloadId(
                        $oRequest->ids,
                        $oService->export($oRequest->source, $oRequest->format, $oRequest->options)
                    );
                    $oModel->setBatchStatus($oRequest->ids, $oModel::STATUS_COMPLETE);
                    $this->oOutput->writeln('Completed <info>' . $oRequest->source . '->' . $oRequest->format . '</info>');

                    //  Send emails in a different try/catch block so if it fails it doesn't mark the report as failed
                    $this->oOutput->writeln('Sending emails');

                    foreach ($oRequest->recipients as $iRecipient) {
                        $this->oOutput->writeln('Sending email to user #<info>' . $iRecipient . '</info>');
                        try {

                            $oSuccessEmail
                                ->to($iRecipient)
                                ->data('source', [
                                    'label'       => $oService->getSourceBySlug($oRequest->source)?->label,
                                    'description' => $oService->getSourceBySlug($oRequest->source)?->description,
                                ])
                                ->data('format', [
                                    'label'       => $oService->getFormatBySlug($oRequest->format)?->label,
                                    'description' => $oService->getFormatBySlug($oRequest->format)?->description,
                                ])
                                ->send();

                        } catch (\Throwable $e) {
                            $this->oOutput->writeln('<error>Email failed to send: ' . $e->getMessage() . '</error>');
                        }
                    }

                } catch (\Exception $e) {
                    $this->executionFailed($e, $oRequest, $oModel, $oFailEmail);
                }
            }

        } else {
            $this->oOutput->writeln('Nothing to do');
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Marks a request as failed, recording why it failed and informs the recipients
     *
     * @param \Exception                                 $oException The exception which was thrown
     * @param \stdClass                                  $oRequest   The current request
     * @param \Nails\Admin\Model\Export                  $oModel     The data export model
     * @param \Nails\Admin\Factory\Email\DataExport\Fail $oEmail     The email object
     */
    protected function executionFailed(
        \Exception $oException,
        \stdClass $oRequest,
        \Nails\Admin\Model\Export $oModel,
        \Nails\Admin\Factory\Email\DataExport\Fail $oEmail
    ) {

        $this->oOutput->writeln('<error>' . get_class($oException) . ': ' . $oException->getMessage() . '</error>');
        $oModel->setBatchStatus($oRequest->ids, $oModel::STATUS_FAILED, $oException->getMessage());

        $oEmail
            ->data('error', $oException->getMessage());

        foreach ($oRequest->recipients as $iRecipient) {
            $oEmail->to($iRecipient)->send();
        }
    }
}
