<?php

use Nails\Common\Service\Asset;
use Nails\Common\Service\View;
use Nails\Config;
use Nails\Environment;
use Nails\Factory;
use Nails\Functions;

/** @var View $oView */
$oView = Factory::service('View');
/** @var \Nails\Admin\Service\Controller $oControllerService */
$oControllerService = Factory::service('Controller', \Nails\Admin\Constants::MODULE_SLUG);

//  Elements closed in another view, helps IDE
echo '<!DOCTYPE html>';
echo '<html lang="en">';

?>
    <head>
        <meta charset="UTF-8" />
        <title>
            <?php

            echo 'Admin - ';
            echo !empty($page->module->name) ? $page->module->name . ' - ' : null;
            echo !empty($page->title) ? $page->title . ' - ' : null;
            echo Config::get('APP_NAME');

            ?>
        </title>
        <meta name="keywords" content="" />
        <meta name="description" content="" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
        <!--    NAILS JS GLOBALS    -->
        <script style="text/javascript">
        /* jshint ignore:start */
        window.ENVIRONMENT = '<?=Environment::get()?>';
        window.SITE_URL = '<?=siteUrl('', Functions::isPageSecure())?>';
        window.NAILS = {
            URL: '<?=Config::get('NAILS_ASSETS_URL')?>',
            LANG: {},
            USER: {
                ID: <?=activeUser('id')?>,
                FNAME: '<?=addslashes(activeUser('first_name'))?>',
                LNAME: '<?=addslashes(activeUser('last_name'))?>',
                EMAIL: '<?=addslashes(activeUser('email'))?>'
            }
        };
        /* jshint ignore:end */
        </script>
        <noscript>
            <style type="text/css">
                .js-only {
                    display: none;
                }
            </style>
        </noscript>
        <!--    ASSETS  -->
        <link href="//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700" rel="stylesheet" type="text/css">
        <link href="//fonts.googleapis.com/css?family=Roboto:400italic,700italic,400,700" rel="stylesheet" type="text/css">
        <?php

        $aColours = array_map(
            fn($sColor) => implode(',', sscanf($sColor, '#%02x%02x%02x')),
            [
                'brand-color-primary'   => appSetting('primary_colour', \Nails\Admin\Constants::MODULE_SLUG) ?: '#171d20',
                'brand-color-secondary' => appSetting('secondary_colour', \Nails\Admin\Constants::MODULE_SLUG) ?: '#2b2d2e',
                'brand-color-highlight' => appSetting('highlight_colour', \Nails\Admin\Constants::MODULE_SLUG) ?: '#d27312',
            ]
        );

        echo '<style type="text/css">';
        echo ':root {';
        foreach ($aColours as $sVar => $sValue) {
            echo sprintf(
                '--%s: %s;',
                $sVar,
                $sValue
            );
        }
        echo '}';
        echo '</style>';

        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset->output($oAsset::TYPE_CSS);
        $oAsset->output($oAsset::TYPE_CSS_INLINE);
        $oAsset->output($oAsset::TYPE_JS_INLINE_HEADER);

        ?>
    </head>
<?php

//  Element closed in another view, helps IDE
echo '<body class="' . (empty($adminControllers) ? 'no-modules' : '') . '  ' . (empty($isModal) ? '' : 'blank') . '">';

?>
    <noscript>
        <strong>We're sorry but admin dashboard doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
    </noscript>
<?php

if (empty($isModal)) {

    ?>
    <div class="topbar admin-vue-app">
        <div class="topbar__logo d-none d-md-flex">
            <?=\Nails\Admin\Helper::logo()?>
        </div>
        <div class="d-md-none u-mr10 u-ml10">
            <menu-toggle></menu-toggle>
        </div>
        <div class="u-flex u-flex-space-b w-100 h-100">
            <div class="topbar__nav">
                <modal-button
                    modal-name="create"
                    class="btn btn__primary u-md-mr5 u-mr15"
                >
                    <i class="fa fa-plus-circle"></i>
                    <span class="btn__label">Create</span>
                </modal-button>
                <modal-button
                    modal-name="search"
                    class="btn btn__primary u-md-mr5 u-mr15"
                >
                    <i class="fa fa-search"></i>
                    <span class="btn__label">Search</span>
                </modal-button>
                <a href="<?=siteUrl()?>" class="btn btn__primary" target="_blank">
                    <i class="fa fa-external-link-alt"></i>
                    <span class="btn__label">View Site</span>
                </a>
            </div>
            <div class="topbar__account">
                <div class="topbar__avatar"
                     style="background-image: url('<?=cdnAvatar(activeUser('id'))?>')"
                ></div>
                <h3 class="topbar__name heading--sm color--white bold  u-md-ml5 u-ml15 u-mb0 d-none d-md-inline">
                    <?=activeUser('name')?>
                </h3>
                <a href="<?=siteUrl(\Nails\Auth\Admin\Controller\Accounts::url('edit/' . activeUser('id')))?>" class="btn btn__secondary u-md-ml5 u-ml15">
                    <i class="fa fa-cog"></i>
                    <span class="btn__label">Edit</span>
                </a>
                <?php

                if (wasAdmin()) {
                    $adminRecovery = getAdminRecoveryData();
                    ?>
                    <a href="<?=$adminRecovery->loginUrl?>" class="btn btn__secondary u-md-ml5 u-ml15">
                        <i class="fa fa-sign-out-alt"></i>
                        Log back in as <?=$adminRecovery->name?>
                    </a>
                    <?php
                }

                ?>
                <a href="<?=siteUrl('auth/logout')?>" class="btn btn__secondary u-md-ml5 u-ml15">
                    <i class="fa fa-user-times"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
    <?php

    //  Element closed in another view, helps IDE
    echo '<div class="main">';

    ?>
    <div class="admin-vue-app">
        <side-nav
            v-bind:menu-items="<?=htmlentities(json_encode($oControllerService->getSidebarGroups()))?>"
        ></side-nav>
    </div>
    <?php

    //  Elements closed in another view, helps IDE
    echo '<div class="content">';
}

$oView
    ->load([
        'admin/_components/structure/header/page-header',
        'admin/_components/structure/header/page-alerts',
    ]);
