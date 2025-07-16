<?php

namespace Nails\Admin\Resource\Dashboard;

use Nails\Common\Resource;

/**
 * Class Widget
 *
 * @package Nails\Admin\Resource\Dashboard
 */
class Widget extends Resource
{
    /** @var string */
    public $slug;

    /** @var int */
    public $x;

    /** @var int */
    public $y;

    /** @var int */
    public $w;

    /** @var int */
    public $h;

    /** @var array */
    public $config;

    // --------------------------------------------------------------------------

    public function __construct(self|\stdClass|array $resource = [])
    {
        parent::__construct($resource);
        $this->config = json_decode($this->config, JSON_OBJECT_AS_ARRAY) ?? [];
    }
}
