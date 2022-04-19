<?php

/**
 * This file is the template for the contents of Admin controllers
 * Used by the console command when creating Admin controllers.
 */

use Nails\Auth\Constants;
use Nails\Auth\Model\User;
use Nails\Auth\Model\User\Group;
use Nails\Factory;

return <<<'EOD'
<?php

namespace {{NAMESPACE}};

use Nails\Admin\Interfaces;
use Nails\Admin\Traits;

/**
 * Class {{CLASS_NAME}}
 *
 * @package {{NAMESPACE}}
 */
class {{CLASS_NAME}} implements Interfaces\Dashboard\Widget
{
    use Traits\Dashboard\Widget;

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return '@todo - write a title';
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return '@todo - write a description';
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getBody(): string
    {
        return '@todo - return body';
    }
}


EOD;
