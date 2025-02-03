<?php

namespace Nails\Admin\Interfaces\DataExport;

use Nails\Admin\DataExport\SourceResponse;

/**
 * Interface Source
 *
 * @package Nails\Admin\Interfaces\DataExport
 */
interface Source
{
    /**
     * Returns the source's label
     *
     * @return string
     */
    public function getLabel(): string;

    // --------------------------------------------------------------------------

    /**
     * Returns the source's filename
     *
     * @return string
     */
    public function getFileName(): string;

    // --------------------------------------------------------------------------

    /**
     * Returns the source's description
     *
     * @return string
     */
    public function getDescription(): string;

    // --------------------------------------------------------------------------

    /**
     * The source's extended description, HTML allowed, optional
     *
     * @return string
     */
    public function getDescriptionExtended(): string;

    // --------------------------------------------------------------------------

    /**
     * Returns an array of additional options for the export
     *
     * @return array
     */
    public function getOptions(): array;

    // --------------------------------------------------------------------------

    /**
     * Provides an opportunity for the source to decide whether it is available or not to the user
     *
     * @return bool
     */
    public function isEnabled(): bool;

    // --------------------------------------------------------------------------

    /**
     * Performs the export; must return a SourceResponse object,
     * or an array of these objects.
     *
     * @param array $aOptions The options, in key/value form
     *
     * @return SourceResponse|array
     */
    public function execute($aOptions = []);
}
