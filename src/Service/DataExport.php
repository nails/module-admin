<?php

/**
 * This service handles exporting data
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Library
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Service;

use Nails\Admin\Constants;
use Nails\Admin\DataExport\SourceResponse;
use Nails\Admin\Interfaces;
use Nails\Admin\Resource\DataExport\Format;
use Nails\Admin\Resource\DataExport\Source;
use Nails\Cdn;
use Nails\Common\Exception\NailsException;
use Nails\Common\Factory\Component;
use Nails\Common\Service\FileCache;
use Nails\Components;
use Nails\Config;
use Nails\Factory;

/**
 * Class DataExport
 *
 * @package Nails\Admin\Service
 */
class DataExport
{
    /**
     * The default data format to use
     *
     * @var string
     */
    const DEFAULT_FORMAT = Constants::MODULE_SLUG . '::Csv';

    /**
     * How long the expiring URL should be valid for, in seconds
     *
     * @var int
     */
    const EXPORT_TTL = 300;

    /**
     * The default retention period for reports, in seconds
     */
    const RETENTION_PERIOD = 3600;

    // --------------------------------------------------------------------------

    /**
     * The available sources
     *
     * @var Source[]
     */
    protected $aSources = [];

    /**
     * The available formats
     *
     * @var Format[]
     */
    protected $aFormats = [];

    /**
     * Any generated cache files
     *
     * @var array
     */
    protected $aCacheFiles = [];

    // --------------------------------------------------------------------------

    /**
     * DataExport constructor.
     */
    public function __construct()
    {
        $this->aSources = [];
        $this->aFormats = [];

        foreach (Components::available() as $oComponent) {

            $aClasses = $oComponent
                ->findClasses('Admin\\DataExport\\Source')
                ->whichImplement(Interfaces\DataExport\Source::class);

            foreach ($aClasses as $sClass) {
                $oInstance        = new $sClass();
                $this->aSources[] = new Source([
                    'slug'                 => $this->generateSlug($oComponent, $sClass),
                    'label'                => $oInstance->getLabel(),
                    'description'          => $oInstance->getDescription(),
                    'description_extended' => $oInstance->getDescriptionExtended(),
                    'options'              => $oInstance->getOptions(),
                    'instance'             => $oInstance,
                ]);
            }

            $aClasses = $oComponent
                ->findClasses('Admin\\DataExport\\Format')
                ->whichImplement(Interfaces\DataExport\Format::class);

            foreach ($aClasses as $sClass) {
                $oInstance        = new $sClass();
                $this->aFormats[] = new Format([
                    'slug'        => $this->generateSlug($oComponent, $sClass),
                    'label'       => $oInstance->getLabel(),
                    'description' => $oInstance->getDescription(),
                    'instance'    => $oInstance,
                ]);
            }
        }

        arraySortMulti($this->aSources, 'label');
        arraySortMulti($this->aFormats, 'label');

        $this->aSources = array_values($this->aSources);
        $this->aFormats = array_values($this->aFormats);
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a slug
     *
     * @param Component $oComponent The component the class belongs to
     * @param string    $sClass     The class to slugify
     *
     * @return string
     */
    protected function generateSlug(Component $oComponent, string $sClass): string
    {
        $sClass = preg_replace('/^.*\\\\Admin\\\\DataExport\\\\(Source|Format)\\\\/', '', $sClass);
        $sClass = str_replace('\\', '', $sClass);

        return sprintf(
            '%s::%s',
            $oComponent->slug,
            $sClass
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Returns all the available sources
     *
     * @return Source[]
     */
    public function getAllSources(): array
    {
        return $this->aSources;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a specific source by its slug
     *
     * @param string|null $sSlug The source's slug
     *
     * @return Source|null
     */
    public function getSourceBySlug(string $sSlug = null): ?Source
    {
        foreach ($this->aSources as $oSource) {
            if ($sSlug === $oSource->slug) {
                return $oSource;
            }
        }

        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns all the available formats
     *
     * @return Format[]
     */
    public function getAllFormats(): array
    {
        return $this->aFormats;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a specific format by its slug
     *
     * @param string|null $sSlug The format's slug
     *
     * @return \stdClass|null
     */
    public function getFormatBySlug($sSlug): ?Format
    {
        foreach ($this->aFormats as $oFormat) {
            if ($sSlug === $oFormat->slug) {
                return $oFormat;
            }
        }

        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Executes a DateExport source then passes to a DataExport format. Once complete
     * the resulting file is uploaded to the CDN and the object's ID returned.
     *
     * @param string $sSourceSlug The slug of the source to use
     * @param string $sFormatSlug The slug of the format to use
     * @param array  $aOptions    Additional options to pass to the source
     *
     * @return int
     * @throws NailsException
     */
    public function export($sSourceSlug, $sFormatSlug, $aOptions = []): int
    {
        $oSource = $this->getSourceBySlug($sSourceSlug);
        if (empty($oSource)) {
            throw new NailsException('Invalid data source "' . $sSourceSlug . '"');
        }

        $oFormat = $this->getFormatBySlug($sFormatSlug);
        if (empty($oFormat)) {
            throw new NailsException('Invalid data format "' . $sFormatSlug . '"');
        }

        $oSourceResponse = $oSource->instance->execute($aOptions);
        if (!is_array($oSourceResponse)) {
            $aSourceResponses = [$oSourceResponse];
        } else {
            $aSourceResponses = $oSourceResponse;
        }

        //  Create temporary working directory
        /** @var FileCache $oFileCache */
        $oFileCache = Factory::service('FileCache');
        $sTempDir   = $oFileCache->getDir() . 'data-export-' . md5(microtime(true)) . mt_rand() . '/';
        mkdir($sTempDir);

        //  Process each file
        $aFiles = [];
        try {

            foreach ($aSourceResponses as $oSourceResponse) {

                if (!($oSourceResponse instanceof SourceResponse)) {
                    throw new NailsException('Source must return an instance of SourceResponse');
                }

                //  Create a new file
                $sFile    = $sTempDir . $oSourceResponse->getFilename() . '.' . $oFormat->instance->getFileExtension();
                $aFiles[] = $sFile;
                $rFile    = fopen($sFile, 'w+');
                //  Write to the file
                $oSourceResponse->reset();
                $oFormat->instance->execute($oSourceResponse, $rFile);
                //  Close the file
                fclose($rFile);
            }

            //  Compress if > 1
            if (count($aFiles) > 1) {
                $sArchiveFile = $sTempDir . 'export.zip';
                $oZip         = Factory::service('Zip');
                foreach ($aFiles as $sFile) {
                    $oZip->read_file($sFile);
                }
                $oZip->archive($sArchiveFile);
                $aFiles[] = $sArchiveFile;
            }
            $sFile = end($aFiles);

            /** @var Cdn\Service\Cdn $oCdn */
            $oCdn    = Factory::service('Cdn', Cdn\Constants::MODULE_SLUG);
            $oObject = $oCdn->objectCreate(
                $sFile,
                [
                    'slug'      => 'data-export',
                    'is_hidden' => true,
                ],
                [
                    'no-md5-check' => true,
                ]
            );

            if (empty($oObject)) {
                throw new NailsException('Failed to upload exported file. ' . $oCdn->lastError());
            }
        } finally {
            //  Tidy up
            foreach ($aFiles as $sFile) {
                if (file_exists($sFile)) {
                    unlink($sFile);
                }
            }
            rmdir($sTempDir);
        }

        return $oObject->id;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns how long the expiring URL for a generated report should be, in seconds
     *
     * @return int
     */
    public function getUrlTtl(): int
    {
        return (int) Config::get('ADMIN_DATA_EXPORT_URL_TTL', static::EXPORT_TTL);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns how long the expiring URL for a generated report should be, in seconds
     *
     * @return int
     */
    public function getRetentionPeriod(): int
    {
        return (int) Config::get('ADMIN_DATA_EXPORT_RETENTION', static::RETENTION_PERIOD);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns whether the export cron job has been run in the past 5 minutes
     *
     * @return bool
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function isRunning(): bool
    {
        $sLastRun   = appSetting('data-export-cron-last-run', Constants::MODULE_SLUG);
        $bIsRunning = false;
        if ($sLastRun) {
            $oNow       = Factory::factory('DateTime');
            $oLastRun   = new \DateTime($sLastRun);
            $iDiff      = $oNow->getTimestamp() - $oLastRun->getTimestamp();
            $bIsRunning = $iDiff <= 300;
        }

        return $bIsRunning;
    }

    // --------------------------------------------------------------------------

    /**
     * Cleans up any generated cache files
     */
    public function __destruct()
    {
        foreach ($this->aCacheFiles as $sCacheFile) {
            @unlink($sCacheFile);
        }
    }
}
