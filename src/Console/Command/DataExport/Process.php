<?php

namespace Nails\Admin\Console\Command\DataExport;

use DateTime;
use Nails\Admin\Admin\Controller\Utilities;
use Nails\Admin\Constants;
use Nails\Admin\Factory\Email\DataExport\Fail;
use Nails\Admin\Factory\Email\DataExport\Success;
use Nails\Admin\Model\Export;
use Nails\Admin\Service\DataExport;
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
            ->setDescription('Processes any pending data export requests');
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
            $this->process();

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

    protected function process()
    {
        $this->oOutput->writeln('Generating exports');

        /** @var DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        setAppSetting('data-export-cron-last-run', Constants::MODULE_SLUG, $oNow->format('Y-m-d H:i:s'));

        /** @var DataExport $oService */
        $oService = Factory::service('DataExport', Constants::MODULE_SLUG);
        /** @var Export $oModel */
        $oModel    = Factory::model('Export', Constants::MODULE_SLUG);
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
                    $aGroupedRequests[$sHash]->recipients[] = $oRequest->created_by;
                    $aGroupedRequests[$sHash]->ids[]        = $oRequest->id;
                } else {
                    $aGroupedRequests[$sHash] = (object) [
                        'source'     => $oRequest->source,
                        'format'     => $oRequest->format,
                        'options'    => json_decode($oRequest->options, JSON_OBJECT_AS_ARRAY),
                        'recipients' => [$oRequest->created_by],
                        'ids'        => [$oRequest->id],
                    ];
                }
            }

            /** @var Success $oSuccessEmail */
            $oSuccessEmail = Factory::factory('EmailDataExportSuccess', Constants::MODULE_SLUG);
            $oSuccessEmail->data(['login_url' => Utilities::url('export')]);
            /** @var Fail $oFailEmail */
            $oFailEmail = Factory::factory('EmailDataExportFail', Constants::MODULE_SLUG);

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

                    try {

                        foreach ($oRequest->recipients as $iRecipient) {
                            $this->oOutput->writeln('Sending email to user #<info>' . $iRecipient . '</info>');
                            $oSuccessEmail
                                ->to($iRecipient)
                                ->send();
                        }

                    } catch (\Exception $e) {
                        $this->oOutput->writeln('<error>Email failed to send: ' . $e->getMessage() . '</error>');
                    }

                } catch (\Exception $e) {
                    $this->executionFailed($e, $oRequest, $oModel, $oFailEmail);
                }
            }

        } else {
            $this->oOutput->writeln('Nothing to do');
        }
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
            ->data([
                'error' => $oException->getMessage(),
            ]);

        foreach ($oRequest->recipients as $iRecipient) {
            $oEmail->to($iRecipient)->send();
        }
    }
}
