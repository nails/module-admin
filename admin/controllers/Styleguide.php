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

namespace Nails\Admin\Admin;

use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;

class Styleguide extends Base
{
    public function index()
    {
        $this->data['page']->title = 'Admin Style Guide';
        Helper::loadView('index');
    }
}