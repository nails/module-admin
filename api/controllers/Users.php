<?php

/**
 * Admin API end points: Users
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Api\Admin;

use Nails\Factory;

class Users extends \Nails\Api\Controller\Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    /**
     * Searches users
     * @return array
     */
    public function getSearch()
    {
        if (!isAdmin()) {

            return [
                'status' => 401,
                'error'  => 'You must be an administrator.',
            ];

        } else {

            $oInput     = Factory::service('Input');
            $oUserModel = Factory::model('User', 'nailsapp/module-auth');
            $avatarSize = $oInput->get('avatarSize') ? $oInput->get('avatarSize') : 50;
            $users      = $oUserModel->search($oInput->get('term'), 1, 50);
            $out        = ['users' => []];

            foreach ($users->data as $user) {

                $temp              = new \stdClass();
                $temp->id          = $user->id;
                $temp->email       = $user->email;
                $temp->first_name  = $user->first_name;
                $temp->last_name   = $user->last_name;
                $temp->gender      = $user->gender;
                $temp->profile_img = cdnAvatar($temp->id, $avatarSize, $avatarSize);

                $out['users'][] = $temp;
            }

            return $out;
        }
    }
}
