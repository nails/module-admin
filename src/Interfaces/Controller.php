<?php

namespace Nails\Admin\Interfaces;

use \Nails\Admin\Factory\Nav;

interface Controller
{
    /**
     * Announces the controller's sidebar Nav Groups
     *
     * @return Nav|Nav[]|null
     */
    public static function announce(): Nav|array|null;

    /**
     * Compiles the URL for the controller, appending the supplied string
     *
     * @param string $sUrl
     *
     * @return string
     */
    public static function url(string $sUrl = ''): string;
}
