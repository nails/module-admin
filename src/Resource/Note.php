<?php

namespace Nails\Admin\Resource;

use Nails\Common\Resource\Entity;

/**
 * Class Note
 *
 * @package Nails\Admin\Resource
 */
class Note extends Entity
{
    /** @var string */
    public $item_model;

    /** @var int */
    public $item_id;

    /** @var string */
    public $message;

    /** @var bool */
    public $is_deleted;
}
