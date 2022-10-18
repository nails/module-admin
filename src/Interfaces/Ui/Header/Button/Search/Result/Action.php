<?php

namespace Nails\Admin\Interfaces\Ui\Header\Button\Search\Result;

interface Action
{
    /**
     * Returns the item's icon, this should be a FontAwesome class name
     */
    public function getIconClass(): string;

    /**
     * Returns the item's label
     */
    public function getLabel(): string;

    /**
     * Returns the item's URL
     */
    public function getUrl(): string;

    /**
     * Returns whether the action should open in a new tab
     */
    public function isNewTab(): bool;
}
