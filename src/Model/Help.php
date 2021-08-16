<?php

/**
 * Admin help model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Model;

use Nails\Common\Model\Base;
use Nails\Config;

class Help extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'admin_help_video';

    // --------------------------------------------------------------------------

    /**
     * This method applies the conditionals which are common across the get_*()
     * methods and the count() method.
     *
     * @param array $aData Data passed from the calling method
     *
     * @return void
     **/
    protected function getCountCommon(array &$aData = []): void
    {
        if (!empty($aData['keywords'])) {

            if (empty($aData['or_like'])) {
                $aData['or_like'] = [];
            }

            $aData['or_like'][] = [
                'column' => $this->tableAlias . '.label',
                'value'  => $aData['keywords'],
            ];
            $aData['or_like'][] = [
                'column' => $this->tableAlias . '.description',
                'value'  => $aData['keywords'],
            ];
        }

        parent::getCountCommon($aData);
    }
}
