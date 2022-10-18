<?php

namespace Nails\Admin\Interfaces\Ui\Header\Button\Search;

interface Section
{
    /**
     * Returns the item's label
     */
    public function getLabel(): string;

    /**
     * Returns the item's label
     *
     * @param string $sQuery The search query
     */
    public function getResults(string $sQuery): array;

    /**
     * Returns the item's order
     */
    public static function getOrder(): int;
}
