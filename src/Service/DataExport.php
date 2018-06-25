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

use Nails\Factory;

/**
 * Class DataExport
 * @package Nails\Admin\Service
 */
class DataExport
{
    protected $aSources    = [];
    protected $aFormats    = [];
    protected $aCacheFiles = [];

    // --------------------------------------------------------------------------

    /**
     * DataExport constructor.
     */
    public function __construct()
    {
        $this->aSources = [];
        $this->aFormats = [];
        $aComponents    = array_merge(
            [
                (object) [
                    'slug'      => 'app',
                    'namespace' => 'App\\',
                    'path'      => FCPATH,
                ],
            ],
            _NAILS_GET_COMPONENTS()
        );

        foreach ($aComponents as $oComponent) {

            $sPath             = $oComponent->path;
            $sNamespace        = $oComponent->namespace;
            $aComponentSources = array_filter((array) directory_map($sPath . 'src/DataExport/Source'));
            $aComponentFormats = array_filter((array) directory_map($sPath . 'src/DataExport/Format'));

            foreach ($aComponentSources as $sSource) {

                $sClass    = $sNamespace . 'DataExport\\Source\\' . basename($sSource, '.php');
                $oInstance = new $sClass();

                if ($oInstance->isEnabled()) {
                    $this->aSources[] = (object) [
                        'slug'        => $oComponent->slug . '::' . basename($sSource, '.php'),
                        'label'       => $oInstance->getLabel(),
                        'description' => $oInstance->getDescription(),
                        'options'     => $oInstance->getOptions(),
                        'instance'    => $oInstance,
                    ];
                }
            }

            foreach ($aComponentFormats as $sFormat) {

                $sClass    = $sNamespace . 'DataExport\\Format\\' . basename($sFormat, '.php');
                $oInstance = new $sClass();

                $this->aFormats[] = (object) [
                    'slug'        => $oComponent->slug . '::' . basename($sFormat, '.php'),
                    'label'       => $oInstance->getLabel(),
                    'description' => $oInstance->getDescription(),
                    'instance'    => $oInstance,
                ];
            }
        }

        arraySortMulti($this->aSources, 'label');
        arraySortMulti($this->aFormats, 'label');

        $this->aSources = array_values($this->aSources);
        $this->aFormats = array_values($this->aFormats);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns all the available sources
     * @return array
     */
    public function getAllSources()
    {
        return $this->aSources;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a specific source by its slug
     *
     * @param $sSlug
     *
     * @return \stdClass|null
     */
    public function getSourceBySlug($sSlug)
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
     * @return array
     */
    public function getAllFormats()
    {
        return $this->aFormats;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a specific format by its slug
     *
     * @param $sSlug
     *
     * @return \stdClass|null
     */
    public function getFormatBySlug($sSlug)
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
     * @return integer
     * @throws \Exception
     */
    public function export($sSourceSlug, $sFormatSlug, $aOptions = [])
    {
        $oSource = $this->getSourceBySlug($sSourceSlug);
        if (empty($oSource)) {
            throw new \Exception('Invalid data source "' . $sSourceSlug . '"');
        }

        $oFormat = $this->getFormatBySlug($sFormatSlug);
        if (empty($oFormat)) {
            throw new \Exception('Invalid data format "' . $sFormatSlug . '"');
        }

        $oSource = $oSource->instance->execute($aOptions);
        if (!is_array($oSource)) {
            $aSources = [$oSource];
        } else {
            $aSources = $oSource;
        }

        //  Create temporary working directory
        $sTempDir = CACHE_PATH . 'data-export-' . md5(microtime(true)) . mt_rand() . '/';
        mkdir($sTempDir);

        //  Process each file
        $aFiles = [];
        try {
            foreach ($aSources as $oSource) {
                //  Create a new file
                $sFile    = $sTempDir . $oSource->getFilename() . '.' . $oFormat->instance->getFileExtension();
                $aFiles[] = $sFile;
                $rFile    = fopen($sFile, 'w+');
                //  Write to the file
                $oSource->reset();
                $oFormat->instance->execute($oSource, $rFile);
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

            //  Save to CDN
            $oCdn    = Factory::service('Cdn', 'nailsapp/module-cdn');
            $oObject = $oCdn->objectCreate($sFile, 'data-export');

            if (empty($oObject)) {
                throw new \Exception('Failed to upload exported file. ' . $oCdn->lastError());
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
     * Cleans up any generated cache files
     */
    public function __destruct()
    {
        foreach ($this->aCacheFiles as $sCacheFile) {
            @unlink($sCacheFile);
        }
    }
}