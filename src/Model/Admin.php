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
use Nails\Common\Exception\FactoryException;
use Nails\Common\Model\Base;
use Nails\Config;
use Nails\Factory;

//  @todo (Pablo 2025-07-14) - This should not be a model
class Admin extends Base
{
    protected Auth\Service\User\Meta $oUserMetaService;
    protected array                  $aJsonFields;

    // --------------------------------------------------------------------------

    /**
     * Admin constructor.
     *
     * @throws FactoryException
     */
    public function __construct()
    {
        parent::__construct();
        $this->oUserMetaService = Factory::service('UserMeta', Auth\Constants::MODULE_SLUG);
        $this->aJsonFields      = [
            'nav_state',
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Sets a piece of admin data
     *
     * @param string   $key    The key to set
     * @param mixed    $value  The value to set
     * @param int|null $userId The user's ID, if null active user is used.
     *
     * @return bool
     */
    public function setAdminData(string $key, mixed $value, ?int $userId = null): bool
    {
        return $this->setUnsetAdminData($key, $value, $userId, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Unsets a piece of admin data
     *
     * @param string   $key    The key to set
     * @param int|null $userId The user's ID, if null active user is used.
     *
     * @return bool
     */
    public function unsetAdminData(string $key, ?int $userId = null): bool
    {
        return $this->setUnsetAdminData($key, null, $userId, false);
    }

    // --------------------------------------------------------------------------

    /**
     * Handles the setting and unsetting of admin data
     *
     * @param string   $key    The key to set
     * @param mixed    $value  The value to set
     * @param int|null $userId The user's ID, if null active user is used.
     * @param bool     $set    Whether the data is being set or unset
     *
     * @return bool
     */
    protected function setUnsetAdminData(string $key, mixed $value, ?int $userId, bool $set): bool
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
        return $this->oUserMetaService->update(
            $this->getUserMetaTable(),
            $userId,
            $existing
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Gets items from the admin data, or the entire array of $key is null
     *
     * @param string|null $key    The key to get
     * @param int|null    $userId The user's ID, if null active user is used.
     *
     * @return mixed
     */
    public function getAdminData(?string $key = null, ?int $userId = null): mixed
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
     * @param int|null $userId The user's ID, if null active user is used.
     *
     * @return bool
     */
    public function clearAdminData(?int $userId): bool
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
     * @param int|null $iUserId The User ID, or null for active user
     *
     * @return int|null
     */
    protected function adminDataGetUserId(?int $iUserId): ?int
    {
        return $iUserId ?? activeUser('id');
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
