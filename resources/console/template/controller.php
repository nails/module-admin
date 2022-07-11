<?php

/**
 * This file is the template for the contents of Admin controllers
 * Used by the console command when creating Admin controllers.
 */

return <<<'EOD'
<?php

/**
 * The {{MODEL_NAME}} Admin controller
 *
 * @package  App
 * @category controller
 */

namespace App\Admin\Controller;

use Nails\Admin\Controller\DefaultController;

class {{MODEL_NAME}} extends DefaultController
{
    const CONFIG_MODEL_NAME     = '{{MODEL_NAME}}';
    const CONFIG_MODEL_PROVIDER = '{{MODEL_PROVIDER}}';
}

EOD;
