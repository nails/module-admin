<?php

namespace Nails\Admin\Model;

use Nails\Admin\Constants;
use Nails\Auth;
use Nails\Common\Exception\ModelException;
use Nails\Common\Model\Base;

class Note extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'admin_note';

    /**
     * The name of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_NAME = 'Note';

    /**
     * The provider of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_PROVIDER = Constants::MODULE_SLUG;

    /**
     * Whether this model uses destructive delete or not
     *
     * @var bool
     */
    const DESTRUCTIVE_DELETE = false;

    /**
     * Sort notes date descending so newest notes appear first
     */
    const DEFAULT_SORT_COLUMN = 'created';
    const DEFAULT_SORT_ORDER  = self::SORT_DESC;

    // --------------------------------------------------------------------------

    /**
     * Note constructor.
     *
     * @throws ModelException
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->hasOne('created_by', 'User', Auth\Constants::MODULE_SLUG, 'created_by');
    }
}
