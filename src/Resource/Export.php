<?php

namespace Nails\Admin\Resource;

use Nails\Auth\Resource\User;
use Nails\Common\Resource\DateTime;
use Nails\Common\Resource\Entity;

/**
 * Class Export
 *
 * @package Nails\Admin\Resource
 */
class Export extends Entity
{
    public ?User     $user;
    public ?int      $user_id;
    public ?string   $method;
    public ?string   $source;
    public ?string   $options;
    public ?string   $format;
    public ?string   $status;
    public ?string   $error;
    public ?string   $download_id;
    public ?DateTime $expires;
}
