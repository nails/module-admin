<?php

namespace Nails\Admin\Interfaces\QuickAction;

/**
 * Interface Action
 *
 * @package Nails\Admin\Interfaces\QuickAction
 */
interface Action
{
    /**
     * Returns the label component of the action
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Returns the sub-label component of the action
     *
     * @return string
     */
    public function getSubLabel(): string;

    /**
     * Returns the URL component of the action
     *
     * @return string
     */
    public function getUrl(): string;
}
