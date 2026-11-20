<?php

namespace Nails\Admin;

/**
 * Class Constants
 *
 * @package Nails\Admin
 */
class Constants
{
    /**
     * The slug for this module
     *
     * @var string
     */
    const MODULE_SLUG = 'nails/module-admin';

    /**
     * The URL prefix for the UI
     */
    const MODULE_URL = 'admin';

    /**
     * Font Awesome class used when a sidebar group, or a header-search link, has no icon.
     * The sidebar applies the same class in SideNav.vue.
     */
    const ICON_FALLBACK = 'fa-cog';
}
