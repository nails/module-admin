<?php

/**
 * This file is the template for the contents of DataExport Sources
 * Used by the console command when creating DataExport Sources
 */

return <<<'EOD'
<?php

namespace App\Admin\DataExport\Source;

use Nails\Admin\Constants;
use Nails\Admin\DataExport\SourceResponse;
use Nails\Admin\Interfaces\DataExport\Source;
use Nails\Common\Exception\FactoryException;
use Nails\Factory;

class {{CLASS_NAME}} implements Source
{
    /**
     * Returns the source's label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return '{{NAME}}';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the source's filename
     *
     * @return string
     */
    public function getFileName(): string
    {
        return '{{FILENAME}}';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the source's description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return '{{DESCRIPTION}}';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the source's extended description, HTML allowed, optional
     *
     * @return string
     */
    public function getDescriptionExtended(): string
    {
        return '';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of additional options for the export
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [];
    }

    // --------------------------------------------------------------------------

    /**
     * Provides an opportunity for the source to decide whether it is available or not to the user
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Performs the export; must return a SourceResponse object,
     * or an array of these objects.
     *
     * @param array $aOptions The options, in key/value form
     *
     * @return SourceResponse|array
     * @throws FactoryException
     */
    public function execute($aOptions = [])
    {
        $oResponse = Factory::factory('DataExportSourceResponse', Constants::MODULE_SLUG)
            ->setFileName($this->getFileName());

        // $oResponse->setData($aData)
        // $oResponse->setSource($oSource);

        return $oResponse;
    }
}

EOD;
