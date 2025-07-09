<?php

namespace Nails\Admin\Resource;

use Nails\Common\Resource\Entity;

/**
 * Class Export
 *
 * @package Nails\Admin\Resource
 */
class Export extends Entity
{
    public ?string $source;
    public ?string $options;
    public ?string $format;
    public ?string $status;
    public ?string $error;
    public ?string $download_id;
}
