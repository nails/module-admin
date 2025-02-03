<?php

namespace Nails\Admin\Resource\DataExport;

use Nails\Admin\Interfaces;
use Nails\Common\Resource;

/**
 * Class Source
 *
 * @package Nails\Admin\Resource\DataExport
 */
class Source extends Resource
{
    /**
     * The source's slug
     *
     * @var string
     */
    public $slug = '';

    /**
     * The source's label
     *
     * @var string
     */
    public $label = '';

    /**
     * The source's description
     *
     * @var string
     */
    public $description = '';

    /**
     * The source's extended description, HTML allowed, optional
     *
     * @var string
     */
    public string $description_extended = '';

    /**
     * The source's options array
     *
     * @var array
     */
    public $options = [];

    /**
     * The source's instance
     *
     * @var Interfaces\DataExport\Source
     */
    public $instance;
}
