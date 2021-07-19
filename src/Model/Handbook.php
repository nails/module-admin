<?php

/**
 * Admin handbook model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Model;

use Nails\Common\Model\Base;
use Nails\Common\Traits\Model\Nestable;
use Nails\Common\Traits\Model\Sortable;

class Handbook extends Base
{
    use Nestable;
    use Sortable;

    const NESTED_URL_NAMESPACE = 'handbook';

    /**
     * Handbook constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->table             = NAILS_DB_PREFIX . 'admin_handbook';
        $this->tableAutoSetSlugs = true;
        $this->destructiveDelete = false;
    }

    // --------------------------------------------------------------------------

    public function describeFields($sTable = null)
    {
        $aFields = parent::describeFields($sTable);

        $aFields['label']->validation[] = 'required';
        $aFields['body']->type          = 'cms_widgets';

        $aFields['parent_id']->label = 'Parent';
        $aFields['parent_id']->class = 'js-searcher';
        $aFields['parent_id']->data  = [
            'api' => 'admin/handbook',
        ];

        return $aFields;
    }

    // --------------------------------------------------------------------------

    protected function formatObject(
        &$oObj,
        array $aData = [],
        array $aIntegers = [],
        array $aBools = [],
        array $aFloats = []
    ) {
        parent::formatObject($oObj, $aData, $aIntegers, $aBools, $aFloats);
        $oObj->url = site_url($this->generateUrl($oObj));
    }
}
