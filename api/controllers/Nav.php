<?php

/**
 * Admin API end points: Nav
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Api\Admin;

use Nails\Factory;

class Nav extends \Nails\Api\Controller\Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    private $isAuthorised;
    private $errorMsg;

    // --------------------------------------------------------------------------

    public function __construct($apiRouter)
    {

        parent::__construct($apiRouter);

        if (!$this->user_model->isAdmin()) {

            $this->isAuthorised = false;
            $this->errorMsg     = 'You must be an administrator.';

        } else {

            $this->isAuthorised = true;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Saves the user's admin nav preferences
     * @return array
     */
    public function postSave()
    {
        if (!$this->isAuthorised) {

            return array(
                'status' => 401,
                'error'  => $this->errorMsg
            );

        } else {

            $prefRaw = $this->input->post('preferences');
            $pref    = new \stdClass();

            foreach ($prefRaw as $module => $options) {

                $pref->{$module}       = new \stdClass();
                $pref->{$module}->open = stringToBoolean($options['open']);
            }

            $oAdminModel = Factory::model('Admin', 'nailsapp/module-admin');
            $oAdminModel->setAdminData('nav_state', $pref);

            return array();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Resets a user's admin nav preferences
     * @return void
     */
    public function postReset()
    {
        if (!$this->isAuthorised) {

            return array(
                'status' => 401,
                'error'  => $this->errorMsg
            );

        } else {

            $oAdminModel = Factory::model('Admin', 'nailsapp/module-admin');
            $oAdminModel->unsetAdminData('nav_state');

            return array();
        }
    }
}
