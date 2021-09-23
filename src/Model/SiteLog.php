<?php

/**
 * Admin site log model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Model;

use Nails\Common\Helper\Directory;
use Nails\Common\Model\Base;
use Nails\Common\Service\Logger;
use Nails\Factory;

/**
 * Class SiteLog
 *
 * @package Nails\Admin\Model
 */
class SiteLog extends Base
{
    /**
     * The log directory
     *
     * @var string
     */
    protected $sLogPath;

    // --------------------------------------------------------------------------

    /**
     * SiteLog constructor.
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function __construct()
    {
        parent::__construct();

        /** @var Logger $oLogger */
        $oLogger = Factory::service('Logger');

        $this->sLogPath = $oLogger->getDir();
    }

    // --------------------------------------------------------------------------

    /**
     * Get a list of log files
     *
     * @return void
     */
    public function getAll($iPage = null, $iPerPage = null, array $aData = [], bool $bIncludeDeleted = false): array
    {
        $aLogFiles = Directory::map($this->sLogPath, null, false);

        arsort($aLogFiles);
        $aLogFiles = array_values($aLogFiles);
        $aOut      = [];

        foreach ($aLogFiles as $sFile) {
            $aOut[] = (object) [
                'date'  => (\DateTime::createFromFormat('U', filectime($this->sLogPath . $sFile)))->format('Y-m-d H:i:s'),
                'file'  => $sFile,
                'lines' => $this->countLines($this->sLogPath . $sFile),
            ];
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * @param string $sFile The log file to read
     *
     * @return array|null
     */
    public function readLog(string $sFile): ?array
    {
        if (!is_file($this->sLogPath . $sFile)) {
            $this->setError('Not a valid log file.');
            return null;
        }

        $fh       = fopen($this->sLogPath . $sFile, 'rb');
        $aOut     = [];
        $iCounter = 0;

        while (!feof($fh)) {

            $iCounter++;
            $sLine = trim(fgets($fh));

            if ($iCounter == 1 || empty($sLine)) {

                continue;
            }
            $aOut[] = $sLine;
        }

        fclose($fh);

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Counts the number of lines in the log file
     *
     * @param string $sFile The log file to count
     *
     * @return int
     */
    protected function countLines($sFile): int
    {
        $fh     = fopen($sFile, 'rb');
        $iLines = 0;

        while (!feof($fh)) {

            $line = fgets($fh);

            if (empty($line)) {
                continue;
            }

            $iLines++;
        }

        fclose($fh);

        //  subtract 1, account for the opening <?php line
        return $iLines - 1;
    }
}
