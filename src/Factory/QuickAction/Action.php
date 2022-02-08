<?php

namespace Nails\Admin\Factory\QuickAction;

/**
 * Interface Action
 *
 * @package Nails\Admin\Interfaces\QuickAction
 */
class Action implements \Nails\Admin\Interfaces\QuickAction\Action
{
    protected string $sLabel;
    protected string $sSubLabel;
    protected string $sUrl;

    // --------------------------------------------------------------------------

    /**
     * @param string $sLabel
     * @param string $sSubLabel
     * @param string $sUrl
     */
    public function __construct(string $sLabel, string $sSubLabel, string $sUrl)
    {
        $this->sLabel    = $sLabel;
        $this->sSubLabel = $sSubLabel;
        $this->sUrl      = $sUrl;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the label component of the action
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->sLabel;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the sub-label component of the action
     *
     * @return string
     */
    public function getSubLabel(): string
    {
        return $this->sSubLabel;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the URL component of the action
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->sUrl;
    }
}
