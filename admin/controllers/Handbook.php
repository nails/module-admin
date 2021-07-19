<?php

/**
 * This class renders the admin handbook
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

use Nails\Admin\Controller\DefaultController;

class Handbook extends DefaultController
{
    const CONFIG_MODEL_NAME     = 'Handbook';
    const CONFIG_MODEL_PROVIDER = 'nails/module-admin';
    const CONFIG_SIDEBAR_GROUP  = 'Handbook';
    const CONFIG_TITLE_SINGLE   = 'Page';
}
