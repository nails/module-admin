<?php

namespace Nails\Admin\Resource\DataExport;

use Nails\Admin\Interfaces;
use Nails\Common\Resource;

/**
 * Class Format
 *
 * @package Nails\Admin\Resource\DataExport
 */
class Format extends Resource
{
    /**
     * The format's slug
     *
     * @var string
     */
    public $slug = '';

    /**
     * The format's label
     *
     * @var string
     */
    public $label = '';

    /**
     * The format's description
     *
     * @var string
     */
    public $description = '';

    /**
     * The format's instance
     *
     * @var Interfaces\DataExport\Format
     */
    public $instance;
}
