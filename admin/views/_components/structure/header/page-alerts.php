
<?php

use Nails\Common\Service\UserFeedback;
use Nails\Factory;

/** @var UserFeedback $oUserFeedback */
$oUserFeedback = Factory::service('UserFeedback');

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
