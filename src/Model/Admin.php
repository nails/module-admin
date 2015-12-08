<?php

/**
 * Admin model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Model;

use Nails\Factory;
use Nails\Common\Model\Base;

class Admin extends Base
{
    protected $oUserMeta;
    protected $aJsonFields;

    // --------------------------------------------------------------------------

    public function __construct()
    {
        $this->oUserMeta   = Factory::model('UserMeta', 'nailsapp/module-auth');
        $this->aJsonFields = array(
            'nav_state'
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Sets a piece of admin data
     * @param  string  $key    The key to set
     * @param  mixed   $value  The value to set
     * @param  mixed   $userId The user's ID, if null active user is used.
     * @return boolean
     */
    public function setAdminData($key, $value, $userId = null)
    {
        return $this->setUnsetAdminData($key, $value, $userId, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Unsets a piece of admin data
     * @param  string  $key    The key to set
     * @param  mixed   $userId The user's ID, if null active user is used.
     * @return boolean
     */
    public function unsetAdminData($key, $userId = null)
    {
        return $this->setUnsetAdminData($key, null, $userId, false);
    }

    // --------------------------------------------------------------------------

    /**
     * Handles the setting and unsetting of admin data
     * @param  string  $key    The key to set
     * @param  mixed   $value  The value to set
     * @param  mixed   $userId The user's ID, if null active user is used.
     * @param  boolean $set    Whether the data is being set or unset
     * @return boolean
     */
    protected function setUnsetAdminData($key, $value, $userId, $set)
    {
        //  Get the user ID
        $userId = $this->adminDataGetUserId($userId);

        //  Get the existing data for this user
        $existing = $this->getAdminData(null, $userId);

        if ($set) {

            //  Set the new key
            if (in_array($key, $this->aJsonFields)) {
                $value = json_encode($value);
            }
            $existing[$key] = $value;

        } else {

            //  Unset the existing key
            $existing[$key] = null;
        }

        //  Save to the DB
        $bResult = $this->oUserMeta->update(
            NAILS_DB_PREFIX . 'user_meta_admin',
            $userId,
            $existing
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Gets items from the admin data, or the entire array of $key is null
     * @param  string $key     The key to set
     * @param  mixed  $userId The user's ID, if null active user is used.
     * @return mixed
     */
    public function getAdminData($key = null, $userId = null)
    {
        //  Get the user ID
        $userId = $this->adminDataGetUserId($userId);

        //  Check if data is already in the cache
        $cacheKey = 'admin-data-' . $userId;
        $cache    = $this->getCache($cacheKey);

        if ($cache) {

            $data = $cache;

        } else {


            $oRow = $this->oUserMeta->get(NAILS_DB_PREFIX . 'user_meta_admin', $userId);

            if (!empty($oRow)) {

                foreach ($oRow as $sKey => &$mValue) {
                    //  Hat-tip: http://stackoverflow.com/a/6041773
                    if (in_array($sKey, $this->aJsonFields)) {
                        $mValue = json_decode($mValue);
                    }
                }
                $data = (array) $oRow;

            } else {

                $data = array();
            }

            $this->setCache($cacheKey, $data);
        }

        // --------------------------------------------------------------------------

        /**
         * If no key is returned, return the entire data array, alternatively return
         * the key if it exists.
         */

        if (is_null($key)) {

            return $data;

        } elseif (isset($data[$key])) {

            return $data[$key];

        } else {

            return null;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Completely clears out the admin array
     * @param  mixed   $userId The user's ID, if null active user is used.
     * @return boolean
     */
    public function clearAdminData($userId)
    {
        //  Get the user ID
        $userId = $this->adminDataGetUserId($userId);

        $bResult = $this->oUserMeta->update(
            NAILS_DB_PREFIX . 'user_meta_admin',
            $userId,
            array(
                'nav_state' => null
            )
        );

        if ($bResult) {
            $this->unsetCache('admin-data-' . $userId);
        }

        return $bResult;
    }

    // --------------------------------------------------------------------------

    /**
     * Extracts the user ID to use
     * @param  int $userId The User ID, or null for active user
     * @return int
     */
    protected function adminDataGetUserId($userId)
    {
        if (is_null($userId)) {

            $userId = activeUser('id');

        } else {

            $userId = $userId;
        }

        return $userId;
    }
}