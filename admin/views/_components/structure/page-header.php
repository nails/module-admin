<?php

//  Page title
if (!empty($page->module->name) && !empty($page->title)) {
    $sPageTitle = $page->module->name . ' &rsaquo; ' . $page->title;
} elseif (empty($page->module->name) && !empty($page->title)) {
    $sPageTitle = $page->title;
} elseif (!empty($page->module->name)) {
    $sPageTitle = $page->module->name;
}

$aHeaderButtons = adminHelper('getHeaderButtons');

if (!empty($sPageTitle) || !empty($aHeaderButtons)) {
    ?>
    <div class="page-title">
        <h1>
            <?php

            echo !empty($sPageTitle) ? $sPageTitle : '';

            if (!empty($aHeaderButtons)) {

                echo '<span class="header-buttons">';
                foreach ($aHeaderButtons as $aButton) {

                    $aClasses = array_filter([
                        'btn',
                        'btn-xs',
                        'btn-' . $aButton['context'],
                        $aButton['confirmTitle'] || $aButton['confirmBody'] ? 'confirm' : '',
                        is_array($aButton['url']) ? 'dropdown-toggle' : '',
                    ]);
                    $aAttr    = array_filter([
                        'class="' . implode(' ', $aClasses) . '"',
                        $aButton['confirmTitle'] ? 'data-title="' . $aButton['confirmTitle'] . '"' : '',
                        $aButton['confirmBody'] ? 'data-body="' . $aButton['confirmBody'] . '"' : '',
                    ]);

                    if (is_array($aButton['url'])) {

                        ?>
                        <div class="btn-group">
                            <button type="button" <?=implode(' ', $aAttr)?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?=$aButton['label']?> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <?php
                                foreach ($aButton['url'] as $sLabel => $sItemUrl) {
                                    ?>
                                    <li>
                                        <a href="<?=siteUrl($sItemUrl)?>">
                                            <?=$sLabel?>
                                        </a>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                        <?php

                    } else {

                        if ($aButton['context'] === 'danger') {
                            $aButton['label'] = '<i class="fa fa-exclamation-triangle"></i>' . $aButton['label'];
                        }

                        echo anchor(
                                $aButton['url'],
                                $aButton['label'],
                                implode(' ', $aAttr)
                            ) . ' ';
                    }
                }
                echo '</span>';
            }

            ?>
        </h1>
    </div>
    <?php
}

$aAlerts = [
    'error'    => ['danger', 'fa-times-circle', 'Sorry, something went wrong.'],
    'success'  => ['success', 'fa-check-circle', 'Success!'],
    'info'     => ['info'],
    'warning'  => ['warning'],

    //  @deprecated (Pablo - 2021-07-22)
    'negative' => ['danger'],
    'positive' => ['success'],
    'message'  => ['warning'],
    'notice'   => ['info'],
];

foreach ($aAlerts as $sType => $aAlert) {

    //  Variable variable
    $sType = (string) $$sType;

    if (!empty($sType)) {

        [$sClass, $sIcon, $sTitle] = array_pad($aAlert, 3, null);

        ?>
        <div class="alert alert-<?=$sClass?>">
            <span class="alert__close">&times;</span>
            <?php

            if (!empty($sTitle)) {
                echo sprintf(
                    '<p><strong>%s %s</strong></p>',
                    $sIcon ? '<b class="alert-icon fa ' . $sIcon . '"></b>' : '',
                    $sTitle
                );
            }

            echo sprintf(
                '<p>%s</p>',
                $sType
            );

            ?>
        </div>
        <?php
    }
}
