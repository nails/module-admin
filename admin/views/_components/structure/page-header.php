<?php

use Nails\Common\Service\UserFeedback;
use Nails\Factory;

/** @var UserFeedback $oUserFeedback */
$oUserFeedback = Factory::service('UserFeedback');

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

$aAlertConf = [
    $oUserFeedback::TYPE_ERROR    => [
        'class' => 'danger',
        'icon'  => 'fa-times-circle',
        'title' => 'Sorry, something went wrong.',
    ],
    $oUserFeedback::TYPE_SUCCESS  => [
        'icon'  => 'fa-check-circle',
        'title' => 'Success!',
    ],
    $oUserFeedback::TYPE_NEGATIVE => [
        'class' => 'danger',
    ],
    $oUserFeedback::TYPE_POSITIVE => [
        'class' => 'success',
    ],
    $oUserFeedback::TYPE_MESSAGE  => [
        'class' => 'warning',
    ],
    $oUserFeedback::TYPE_NOTICE   => [
        'class' => 'info',
    ],
];


foreach ($oUserFeedback->getTypes() as $sType) {

    $sValue = (string) $oUserFeedback->get($sType);

    if (!empty($sValue)) {

        $sClass = $aAlertConf[$sType]['class'] ?? strtolower($sType);
        $sIcon  = $aAlertConf[$sType]['info'] ?? null;
        $sTitle = $aAlertConf[$sType]['title'] ?? null;

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
                $sValue
            );

            ?>
        </div>
        <?php
    }
}
