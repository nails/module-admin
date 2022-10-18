<?php

namespace Nails\Admin\Interfaces\Ui\Header\Button\Search;

use Nails\Admin\Interfaces\Ui\Header\Button\Search\Result\Action;

interface Result
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
     * Returns the item's order
     */
    public function getOrder(): int;

    /**
     * Returns the item's actions
     *
     * @return Action[]
     */
    public function getActions(): array;
}
