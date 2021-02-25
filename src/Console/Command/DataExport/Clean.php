<?php

namespace Nails\Admin\Console\Command\DataExport;

use Nails\Admin\Constants;
use Nails\Admin\Model\Export;
use Nails\Admin\Service\DataExport;
use Nails\Cdn\Service\Cdn;
use Nails\Common\Exception\NailsException;
use Nails\Common\Service\Database;
use Nails\Console\Command\Base;
use Nails\Console\Exception\ConsoleException;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Clean
 *
 * @package Nails\Admin\Console\Command\DataExport
 */
class Clean extends Base
{
    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('admin:dataexport:clean')
            ->setDescription('Cleans old data exports according to data retention rules');
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

        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var \DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        /** @var DataExport $oExportService */
        $oExportService = Factory::service('DataExport', Constants::MODULE_SLUG);
        /** @var Export $oModel */
        $oModel = Factory::model('Export', Constants::MODULE_SLUG);
        /** @var Cdn $oCdn */
        $oCdn = Factory::service('Cdn', \Nails\Cdn\Constants::MODULE_SLUG);

        // --------------------------------------------------------------------------

        try {

            $this->banner('Data Export Clean');

            $iRetention = $oExportService->getRetentionPeriod();
            if ($iRetention) {

                $oOutput->writeln('Retention policy: <info>' . $iRetention . ' seconds</info>');
                $oOutput->writeln('Time now is <comment>' . $oNow->format('Y-m-d H:i:s') . '</comment>');
                $oNow->sub(new \DateInterval('PT' . $iRetention . 'S'));
                $oOutput->writeln('Cleaning items older than <comment>' . $oNow->format('Y-m-d H:i:s') . '</comment>');

                $aToClean = $oModel->getAll([
                    'where' => [
                        ['modified <', $oNow->format('Y-m-d H:i:s')],
                    ],
                ]);

                if (!empty($aToClean)) {

                    $oOutput->writeln('Cleaning <info>' . count($aToClean) . '</info> items');
                    foreach ($aToClean as $oExport) {
                        try {

                            $oDb->transaction()->start();
                            $oOutput->write('Cleaning export <info>#' . $oExport->id . '</info>... ');

                            $oModel->delete($oExport->id);

                            if (!empty($oExport->download_id)) {
                                if (!$oCdn->objectDestroy($oExport->download_id)) {
                                    throw new NailsException(
                                        'Failed to delete object. ' . $oCdn->lastError()
                                    );
                                }
                            }

                            $oDb->transaction()->commit();
                            $oOutput->writeln('<info>done</info>');

                        } catch (\Exception $e) {
                            $oDb->transaction()->rollback();
                            $oOutput->writeln('<error>' . $e->getMessage() . '</error>');
                        }
                    }

                } else {
                    $oOutput->writeln('Nothing to clean');
                }

            } else {
                $oOutput->writeln('Data Export cleanup disabled');
            }

        } catch (ConsoleException $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                [$e->getMessage()]
            );
        }

        // --------------------------------------------------------------------------

        //  And we're done
        $oOutput->writeln('');
        $oOutput->writeln('Complete!');

        return self::EXIT_CODE_SUCCESS;
    }
}
