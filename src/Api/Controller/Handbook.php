<?php

/**
 * Admin API end points: Handbook
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Api\Controller;

use Nails\Api\Controller\CrudController;

class Handbook extends CrudController
{
    const REQUIRE_AUTH          = true;
    const CONFIG_MODEL_NAME     = 'Handbook';
    const CONFIG_MODEL_PROVIDER = 'nails/module-admin';
}
