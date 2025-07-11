<?php

use Nails\Common\Constants;
use Nails\Common\Service\Asset;
use Nails\Common\Service\View;
use Nails\Factory;

/** @var Asset $oAsset */
$oAsset = Factory::service('Asset');
/** @var View $oView */
$oView = Factory::service('View');

$oAsset
    ->clear()
    ->load('nails.min.css', Constants::MODULE_SLUG);

$oView
    ->load('structure/header/blank');

?>
    <div class="nails-admin not-found center-screen">
        <div class="panel">
            <div class="panel__header">
                <h1 class="panel__title text-center">
                    404 Page Not Found
                </h1>
            </div>
            <div class="panel__body text-center">
                The page you requested was not found, or your request was invalid.
            </div>
        </div>
    </div>
<?php

$oView->load('structure/footer/blank');
