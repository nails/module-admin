<?php

namespace Nails\Admin\Interfaces;

/**
 * Interface QuickAction
 *
 * @package Nails\Admin\Interfaces
 */
interface QuickAction
{
    /**
     * @param string $sQuery
     * @param string $sOrigin
     *
     * @return \Nails\Admin\Interfaces\QuickAction\Action[]
     */
    public function getActions(string $sQuery, string $sOrigin): array;
}
