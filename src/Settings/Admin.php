<?php

namespace Nails\Admin\Settings;

use Nails\Admin\Admin\Permission;
use Nails\Common\Helper\Form;
use Nails\Common\Interfaces;
use Nails\Common\Service\Input;
use Nails\Components\Setting;
use Nails\Factory;

/**
 * Class Admin
 *
 * @package Nails\Admin\Settings
 */
class Admin implements Interfaces\Component\Settings
{
    const KEY_PRIMARY_COLOUR   = 'primary_colour';
    const KEY_SECONDARY_COLOUR = 'secondary_colour';
    const KEY_HIGHLIGHT_COLOUR = 'highlight_colour';
    const KEY_IP_WHITELIST     = 'whitelist';

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'Admin';
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getPermission(): string
    {
        return Permission\Settings\Admin::class;
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        /** @var Input $oInput */
        $oInput = \Nails\Factory::service('Input');

        /** @var Setting $oBrandingPrimaryColour */
        $oBrandingPrimaryColour = Factory::factory('ComponentSetting');
        $oBrandingPrimaryColour
            ->setKey(static::KEY_PRIMARY_COLOUR)
            ->setLabel('Primary Colour')
            ->setFieldset('Branding')
            ->setPlaceholder('Specify a valid CSS colour value, i.e a hex code or rgb()');

        /** @var Setting $oBrandingSecondaryColour */
        $oBrandingSecondaryColour = Factory::factory('ComponentSetting');
        $oBrandingSecondaryColour
            ->setKey(static::KEY_SECONDARY_COLOUR)
            ->setLabel('Secondary Colour')
            ->setFieldset('Branding')
            ->setPlaceholder('Specify a valid CSS colour value, i.e a hex code or rgb()');

        /** @var Setting $oBrandingHighlightColour */
        $oBrandingHighlightColour = Factory::factory('ComponentSetting');
        $oBrandingHighlightColour
            ->setKey(static::KEY_HIGHLIGHT_COLOUR)
            ->setLabel('Highlight Colour')
            ->setFieldset('Branding')
            ->setPlaceholder('Specify a valid CSS colour value, i.e a hex code or rgb()');

        /** @var Setting $oIpWhitelist */
        $oIpWhitelist = Factory::factory('ComponentSetting');
        $oIpWhitelist
            ->setKey(static::KEY_IP_WHITELIST)
            ->setType(Form::FIELD_TEXTAREA)
            ->setLabel('Allowed IPs')
            ->setFieldset('IP Whitelist')
            ->setPlaceholder('Specify IP addresses to whitelist either comma seperated or on new lines.')
            ->setInfo('Your current IP address is: <code>' . $oInput->ipAddress() . '</code>')
            ->setRenderFormatter(function ($mValue) {
                return is_array($mValue) ? implode(PHP_EOL, $mValue) : '';
            })
            ->setSaveFormatter(function ($mValue) {
                return $this->prepareWhitelist($mValue);
            });

        return [
            $oBrandingPrimaryColour,
            $oBrandingSecondaryColour,
            $oBrandingHighlightColour,
            $oIpWhitelist,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Takes a multi line input and converts it into an array
     *
     * @param string $sInput The input string
     *
     * @return array
     */
    protected function prepareWhitelist($sInput)
    {
        $sWhitelistRaw = $sInput;
        $sWhitelistRaw = str_replace("\n\r", "\n", $sWhitelistRaw);
        $aWhitelistRaw = explode("\n", $sWhitelistRaw);
        $aWhitelist    = [];

        foreach ($aWhitelistRaw as $sLine) {
            $aWhitelist = array_merge(explode(',', $sLine), $aWhitelist);
        }

        $aWhitelist = array_map('trim', $aWhitelist);
        $aWhitelist = array_unique($aWhitelist);
        $aWhitelist = array_filter($aWhitelist);
        $aWhitelist = array_values($aWhitelist);

        return $aWhitelist;
    }
}
