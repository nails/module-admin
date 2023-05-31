<?php

/**
 * Admin model
 *
 * @package                   Nails
 * @subpackage                module-admin
 * @category                  Model
 * @author                    Nails Dev Team
 * @todo (Pablo - 2019-03-22) - This isn't really a model and should be moved to a service
 */

namespace Nails\Admin\Model;

use Nails\Auth;
use Nails\Common\Model\Base;
use Nails\Config;
use Nails\Factory;

class Admin extends Base
{
    protected $oUserMetaService;
    protected $aJsonFields;

    // --------------------------------------------------------------------------

    /**
     * Admin constructor.
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function __construct()
    {
        $this->oUserMetaService = Factory::service('UserMeta', Auth\Constants::MODULE_SLUG);
        $this->aJsonFields      = [
            'nav_state',
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Sets a piece of admin data
     *
     * @param string $key    The key to set
     * @param mixed  $value  The value to set
     * @param mixed  $userId The user's ID, if null active user is used.
     *
     * @return boolean
     */
    public function setAdminData($key, $value, $userId = null)
    {
        return $this->setUnsetAdminData($key, $value, $userId, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Unsets a piece of admin data
     *
     * @param string $key    The key to set
     * @param mixed  $userId The user's ID, if null active user is used.
     *
     * @return boolean
     */
    public function unsetAdminData($key, $userId = null)
    {
        return $this->setUnsetAdminData($key, null, $userId, false);
    }

    // --------------------------------------------------------------------------

    /**
     * Handles the setting and unsetting of admin data
     *
     * @param string  $key    The key to set
     * @param mixed   $value  The value to set
     * @param mixed   $userId The user's ID, if null active user is used.
     * @param boolean $set    Whether the data is being set or unset
     *
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
        $bResult = $this->oUserMetaService->update(
            $this->getUserMetaTable(),
            $userId,
            $existing
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Gets items from the admin data, or the entire array of $key is null
     *
     * @param string $key    The key to set
     * @param mixed  $userId The user's ID, if null active user is used.
     *
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

            $oRow = $this->oUserMetaService->get($this->getUserMetaTable(), $userId);

            if (!empty($oRow)) {

                foreach ($oRow as $sKey => &$mValue) {
                    //  Hat-tip: http://stackoverflow.com/a/6041773
                    if (in_array($sKey, $this->aJsonFields)) {
                        $mValue = json_decode((string) $mValue);
                    }
                }
                $data = (array) $oRow;

            } else {

                $data = [];
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
     *
     * @param mixed $userId The user's ID, if null active user is used.
     *
     * @return boolean
     */
    public function clearAdminData($userId)
    {
        //  Get the user ID
        $userId = $this->adminDataGetUserId($userId);

        $bResult = $this->oUserMetaService->update(
            $this->getUserMetaTable(),
            $userId,
            [
                'nav_state' => null,
            ]
        );

        if ($bResult) {
            $this->unsetCache('admin-data-' . $userId);
        }

        return $bResult;
    }

    // --------------------------------------------------------------------------

    /**
     * Extracts the user ID to use
     *
     * @param int $userId The User ID, or null for active user
     *
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

    // --------------------------------------------------------------------------

    /**
     * Returns the table for admin user meta
     *
     * @return string
     */
    public function getUserMetaTable(): string
    {
        return Config::get('NAILS_DB_PREFIX') . 'user_meta_admin';
    }
}
