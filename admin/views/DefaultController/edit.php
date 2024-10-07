<?php

use Nails\Admin\Helper;
use Nails\Common\Service\Input;
use Nails\Factory;

/** @var Input $oInput */
$oInput = Factory::service('Input');

$aTabs = [];
foreach ($aFieldSets as $sLabel => $aFields) {
    $aTabs[] = [
        'label'   => $sLabel,
        'content' => function () use ($aFields) {

            if (is_string($aFields)) {
                echo $aFields;

            } elseif ($aFields instanceof \Closure) {
                echo call_user_func($aFields, $oItem ?? null);

            } else {
                foreach ($aFields as $iIndex => $oField) {
                    if (empty($oField->key)) {
                        throw new \Nails\Common\Exception\NailsException(
                            sprintf(
                                'Property "key" is missing for field "%s"',
                                $oField->label ?: $iIndex
                            )
                        );

                    } elseif (is_callable('\Nails\Common\Helper\Form\Field::' . $oField->type)) {
                        echo call_user_func('\Nails\Common\Helper\Form\Field::' . $oField->type, (array) $oField);

                    } else {
                        echo Nails\Common\Helper\Form\Field::text((array) $oField);
                    }
                }
            }
        },
    ];
}

if (!empty($oItem)) {
    ?>
    <div class="js-admin-session--also-here" data-prefix="Another user is currently editing this item:"></div>
    <?php
}
?>
<div class="group-defaultcontroller edit" <?=$CONFIG['EDIT_PAGE_ID'] ? 'id="' . $CONFIG['EDIT_PAGE_ID'] . '"' : ''?>>
    <?php

    echo $CONFIG['EDIT_HTML_HEADER'];
    echo form_open();
    echo Helper::tabs($aTabs);
    echo Helper::floatingControls($CONFIG['FLOATING_CONFIG']);
    echo form_close();
    echo $CONFIG['EDIT_HTML_HEADER'];

    ?>
</div>
