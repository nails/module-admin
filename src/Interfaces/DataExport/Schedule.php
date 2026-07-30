<?php

namespace Nails\Admin\Interfaces\DataExport;

use Nails\Auth\Resource\User;

/**
 * Interface Schedule
 *
 * @package Nails\Admin\Interfaces\DataExport
 */
interface Schedule
{
    public function getCronExpression(): string;

    public function getSource(): string;

    public function getFormat(): string;

    public function getOptions(): array;

    /**
     * @return User[]
     */
    public function getUsers(): array;
}
