<?php

namespace Nails\Admin\Interfaces;

/**
 * Interface QuickAction
 *
 * @package Nails\Admin\Interfaces
 */
interface QuickAction
{
    public function getActions(string $sQuery, string $sOrigin): array;
}
