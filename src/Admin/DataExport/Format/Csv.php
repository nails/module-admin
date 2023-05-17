<?php

namespace Nails\Admin\Admin\DataExport\Format;

use Nails\Admin\DataExport\SourceResponse;
use Nails\Admin\Interfaces\DataExport\Format;

/**
 * Class Csv
 * @package Nails\Auth\DataFormat
 */
class Csv implements Format
{
    /**
     * Returns the format's label
     * @return string
     */
    public function getLabel()
    {
        return 'CSV';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's description
     * @return string
     */
    public function getDescription()
    {
        return 'Easily imports to many software packages, including Microsoft Excel.';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's file extension
     * @return string
     */
    public function getFileExtension()
    {
        return 'csv';
    }

    // --------------------------------------------------------------------------

    /**
     * Takes a SourceResponse object and transforms it into the appropriate format.
     *
     * @param SourceResponse $oSourceResponse A SourceResponse object
     * @param resource       $rFile           The file resource to write to
     */
    public function execute($oSourceResponse, $rFile)
    {
        //  Write the field names to the file
        fwrite($rFile, $this->formatRow($oSourceResponse->getFields()));

        //  Write the data to the file
        while ($oRow = $oSourceResponse->getNextItem()) {
            fwrite($rFile, $this->formatRow($oRow));
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Escapes items to be written to the CSV
     *
     * @param \stdClass|array $oRow The row to format
     *
     * @return string
     */
    protected function formatRow($oRow)
    {
        $aItems = array_map(
            function ($sItem) {
                return str_replace('"', '""', trim((string) $sItem));
            },
            (array) $oRow
        );

        return '"' . implode('","', $aItems) . '"' . "\n";
    }
}
