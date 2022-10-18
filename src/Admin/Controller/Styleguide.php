<?php

/**
 * This class renders the admin styleguide
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin\Controller;

use Nails\Admin\Controller\Base;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Helper;

class Styleguide extends Base
{
    public static function announce()
    {
        return null;
    }

    public function index()
    {
        $this
            ->setTitles(['Admin Style Guide'])
            ->loadView('index');
    }
}
