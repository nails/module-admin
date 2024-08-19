<?php

/**
 * Admin changelog model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Model;

use Nails\Admin\Constants;
use Nails\Auth;
use Nails\Common\Events;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Model\Base;
use Nails\Common\Service\Event;
use Nails\Factory;

/**
 * Class ChangeLog
 *
 * @package Nails\Admin\Model
 */
class ChangeLog extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'admin_changelog';

    /**
     * The name of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_NAME = 'ChangeLog';

    /**
     * The provider of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_PROVIDER = Constants::MODULE_SLUG;

    // --------------------------------------------------------------------------

    /**
     * Supported operations
     */
    const OPERATION_CREATE  = 'CREATE';
    const OPERATION_EDIT    = 'EDIT';
    const OPERATION_DELETE  = 'DELETE';
    const OPERATION_RESTORE = 'RESTORE';

    // --------------------------------------------------------------------------

    /**
     * The chnages which are to be saved
     *
     * @var array
     */
    protected $aChanges = [];

    // --------------------------------------------------------------------------

    /**
     * ChangeLog constructor.
     *
     * @throws FactoryException
     * @throws ModelException
     * @throws NailsException
     * @throws \ReflectionException
     */
    public function __construct()
    {
        parent::__construct();

        $this->hasOne('user', 'User', Auth\Constants::MODULE_SLUG);

        /** @var Event $oEventService */
        $oEventService = Factory::service('Event');
        $oEventService->subscribe(
            Events::SYSTEM_SHUTDOWN,
            Events::getEventNamespace(),
            [$this, 'save']
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the searchable columns for this module
     *
     * @return string[]
     */
    public function getSearchableColumns(): array
    {
        return [
            'user_id',
            'operation',
            'item',
            'item_id',
            'title',
            'url',
            'changes',
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new changelog item
     *
     * @param string  $sOperation The operation performed
     * @param string  $sItem      The class of item being tracked
     * @param integer $iItemId    The item's ID (e.g the blog post's ID)
     * @param string  $sTitle     The title of the item (e.g the blog post's title)
     * @param string  $sUrl       The url to the item (e.g the blog post's URL)
     * @param string  $sField
     * @param mixed   $mOldValue  The old value
     * @param mixed   $mNewValue  The new value
     * @param boolean $bStrict    Whether or not to compare $mOldValue and $mNewValue strictly
     * @param boolean $bForce     Whether to force the changelog (i.e do not discard identical values)
     *
     * @return bool
     * @throws FactoryException
     * @throws ModelException
     */
    public function add(
        $sOperation,
        $sItem,
        $iItemId,
        $sTitle,
        $sUrl = null,
        $sField = null,
        $mOldValue = null,
        $mNewValue = null,
        $bStrict = true,
        $bForce = false
    ) {
        /**
         * if the old_value and the new_value are the same then why are you logging
         * a change!? Lazy [read: efficient] dev.
         */

        if (!is_null($sField)) {

            if (!is_string($mNewValue)) {
                $mNewValue = print_r($mNewValue, true);
            }

            if (!is_string($mOldValue)) {
                $mOldValue = print_r($mOldValue, true);
            }

            $mNewValue = trim($mNewValue);
            $mOldValue = trim($mOldValue);

            if (!$bForce && $bStrict && $mNewValue === $mOldValue) {
                return false;
            } elseif (!$bForce && $mNewValue == $mOldValue) {
                return false;
            }
        }

        // --------------------------------------------------------------------------

        /**
         * Define the key for this change; keys should be common across identical
         * items so we can group changes of the same item together.
         */
        $key = md5(activeUser('id') . '|' . $sOperation . '|' . $sItem . '|' . $iItemId . '|' . $sTitle . '|' . $sUrl);

        if (empty($this->aChanges[$key])) {
            $this->aChanges[$key] = [
                'user_id'   => activeUser('id') ? activeUser('id') : null,
                'operation' => $sOperation,
                'item'      => $sItem,
                'item_id'   => $iItemId,
                'title'     => $sTitle,
                'url'       => $sUrl,
                'changes'   => [],
            ];
        }

        // --------------------------------------------------------------------------

        /**
         * Generate a subkey, so that multiple calls to the same field overwrite
         * each other
         */
        if ($sField) {
            $this->aChanges[$key]['changes'][md5($sField)] = (object) [
                'field'     => $sField,
                'old_value' => $mOldValue,
                'new_value' => $mNewValue,
            ];
        }

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Save the changelog items
     *
     * @throws FactoryException
     * @throws ModelException
     */
    public function save()
    {
        //  Process all the items and save to the DB, then clean up
        if ($this->aChanges) {

            $this->aChanges = array_values($this->aChanges);
            $oDate          = Factory::factory('DateTime');

            for ($i = 0; $i < count($this->aChanges); $i++) {
                $this->aChanges[$i]['changes']                    = array_values($this->aChanges[$i]['changes']);
                $this->aChanges[$i]['changes']                    = json_encode($this->aChanges[$i]['changes']);
                $this->aChanges[$i][$this->getColumnCreated()]    = $oDate->format('Y-m-d H:i:s');
                $this->aChanges[$i][$this->getColumnCreatedBy()]  = activeUser('id');
                $this->aChanges[$i][$this->getColumnModified()]   = $oDate->format('Y-m-d H:i:s');
                $this->aChanges[$i][$this->getColumnModifiedBy()] = activeUser('id');
            }

            $oDb = Factory::service('Database');
            $oDb->insert_batch($this->getTableName(), $this->aChanges);
        }

        $this->clear();
    }

    // --------------------------------------------------------------------------

    /**
     * Clear all recorded changes
     */
    public function clear()
    {
        $this->aChanges = [];
    }
}
