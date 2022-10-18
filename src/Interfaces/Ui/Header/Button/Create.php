<?php

namespace Nails\Admin\Interfaces\Ui\Header\Button;

interface Create
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
     * Returns the item's description
     */
    public function getDescription(): string;

    /**
     * Returns the item's URL
     */
    public function getUrl(): string;

    /**
     * Returns the item's order
     */
    public static function getOrder(): int;
}
