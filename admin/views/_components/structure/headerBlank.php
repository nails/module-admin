<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>
        <?php

        echo lang('admin_word_short') . ' - ';
        echo isset($page->module->name) ? $page->module->name . ' - ' : null;
        echo isset($page->title) ? $page->title . ' - ' : null;
        echo \Nails\Factory::service('MetaData')->getAppName();

        ?>
    </title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <!--    NAILS JS GLOBALS    -->
    <script style="text/javascript">
    window.ENVIRONMENT = '<?=\Nails\Environment::get()?>';
    window.SITE_URL = '<?=siteUrl('', \Nails\Functions::isPageSecure())?>';
    window.NAILS = {
        URL: '<?=\Nails\Config::get('NAILS_ASSETS_URL')?>',
        LANG: {},
        USER: {
            ID: <?=activeUser('id')?>,
            FNAME: '<?=addslashes(activeUser('first_name'))?>',
            LNAME: '<?=addslashes(activeUser('last_name'))?>',
            EMAIL: '<?=addslashes(activeUser('email'))?>'
        }
    };
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
    <?php

    $oAsset = \Nails\Factory::service('Asset');
    $oAsset->output('CSS');
    $oAsset->output('CSS-INLINE');
    $oAsset->output('JS-INLINE-HEADER');

    $_primary   = appSetting('primary_colour', 'admin') ?: '#171D20';
    $_secondary = appSetting('secondary_colour', 'admin') ?: '#515557';
    $_highlight = appSetting('highlight_colour', 'admin') ?: '#F09634';

    ?>
    <style type="text/css">

        .admin-branding-text-primary {
            color: <?=$_primary?>;
        }

        .admin-branding-background-primary {
            background: <?=$_primary?>;
        }

        .admin-branding-text-secondary {
            color: <?=$_secondary?>;
        }

        .admin-branding-background-secondary {
            background: <?=$_secondary?>;
        }

        .admin-branding-text-highlight {
            color: <?=$_highlight?>;
        }

        .admin-branding-background-highlight {
            background: <?=$_highlight?>;
        }

        table thead tr th,
        table tfoot tr th {
            background-color: <?=$_primary?>;
        }

    </style>
</head>
<body class="blank">
<?php

\Nails\Factory::service('View')
    ->load('admin/_components/structure/page-header');
