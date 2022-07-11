<?php

namespace Nails\Admin\Interfaces;

use \Nails\Admin\Factory\Nav;

interface Controller
{
    /**
     * Announces the controller's sidebar Nav Groups
     *
     * @return Nav|Nav[]
     */
    public static function announce();

    /**
     * Returns an array of permissions which can be configured for the user
     *
     * @return string[string]
     */
    public static function permissions(): array;

    /**
     * Compiles the URL for the controller, appending the supplied string
     *
     * @param string $sUrl
     *
     * @return string
     */
    public static function url(string $sUrl = ''): string;
}
