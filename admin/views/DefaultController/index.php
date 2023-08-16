<?php

use Nails\Admin\Helper;
use Nails\Common\Helper\ArrayHelper;
use Nails\Common\Service\Mustache;
use Nails\Factory;

/** @var Mustache $oMustache */
$oMustache = Factory::service('Mustache');

?>
<div class="group-defaultcontroller browse" <?=$CONFIG['INDEX_PAGE_ID'] ? 'id="' . $CONFIG['INDEX_PAGE_ID'] . '"' : ''?>>
    <?=adminHelper('loadSearch', $search)?>
    <?=adminHelper('loadPagination', $pagination)?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <?php
                    //  @todo (Pablo - 2018-11-05) - Tidy this nesting up a little
                    foreach ($CONFIG['INDEX_FIELDS'] as $sLabel => $sProperty) {

                        $sNormalisedLabel = strtolower($sLabel);
                        $sNormalisedLabel = preg_replace('/[^a-z0-9 \-_]/', '', $sNormalisedLabel);
                        $sNormalisedLabel = str_replace([' ', '_'], '-', $sNormalisedLabel);

                        $bIsBoolCell = ArrayHelper::inArray([
                            $sProperty,
                            $sNormalisedLabel,
                        ], $CONFIG['INDEX_BOOL_FIELDS']);

                        $bIsUserCell = ArrayHelper::inArray([
                            $sProperty,
                            $sNormalisedLabel,
                        ], $CONFIG['INDEX_USER_FIELDS']);

                        $bIsCenteredCell = ArrayHelper::inArray([
                            $sProperty,
                            $sNormalisedLabel,
                        ], $CONFIG['INDEX_CENTERED_FIELDS']);

                        $aAttr = [
                            'class' => ['field', 'field--' . $sNormalisedLabel],
                        ];

                        if ($bIsBoolCell) {
                            $aAttr['width']   = 150;
                            $aAttr['class'][] = 'boolean';
                        } elseif ($bIsUserCell) {
                            $aAttr['width'] = 300;
                        } elseif ($bIsCenteredCell) {
                            $aAttr['class'][] = 'text-center';
                        }

                        $sAttr = '';
                        foreach ($aAttr as $sKey => $mValue) {
                            if (is_array($mValue)) {
                                $sAttr .= ' ' . $sKey . '="' . implode(' ', $mValue) . '"';
                            } else {
                                $sAttr .= ' ' . $sKey . '="' . $mValue . '"';
                            }
                        }
                        ?>
                        <th <?=$sAttr?>>
                            <?=$sLabel?>
                        </th>
                        <?php
                    }
                    ?>
                    <th class="actions" style="width:175px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($items)) {
                    foreach ($items as $oItem) {
                        ?>
                        <tr>
                            <?php

                            foreach ($CONFIG['INDEX_FIELDS'] as $sLabel => $sProperty) {

                                $sNormalisedLabel = strtolower($sLabel);
                                $sNormalisedLabel = preg_replace('/[^a-z0-9 \-_]/', '', $sNormalisedLabel);
                                $sNormalisedLabel = str_replace([' ', '_'], '-', $sNormalisedLabel);

                                $bIsBoolCell = ArrayHelper::inArray([
                                    $sProperty,
                                    $sNormalisedLabel,
                                ], $CONFIG['INDEX_BOOL_FIELDS']);

                                $bIsUserCell = ArrayHelper::inArray([
                                    $sProperty,
                                    $sNormalisedLabel,
                                ], $CONFIG['INDEX_USER_FIELDS']);

                                $bIsNumeric = ArrayHelper::inArray([
                                    $sProperty,
                                    $sNormalisedLabel,
                                ], $CONFIG['INDEX_NUMERIC_FIELDS']);

                                $bIsCenteredCell = ArrayHelper::inArray([
                                    $sProperty,
                                    $sNormalisedLabel,
                                ], $CONFIG['INDEX_CENTERED_FIELDS']);

                                if (is_object($sProperty) && ($sProperty instanceof \Closure)) {

                                    $mValue = $sProperty($oItem);

                                    if (is_array($mValue)) {
                                        $sCellClass = ArrayHelper::get(1, $mValue);
                                        $mValue     = ArrayHelper::get(0, $mValue);
                                    } else {
                                        $sCellClass = null;
                                    }

                                    $aClasses = [
                                        $sCellClass ?? null,
                                    ];

                                    if ($bIsNumeric && !$bIsUserCell) {
                                        $mValue = number_format($mValue);
                                    }

                                    if ($bIsCenteredCell) {
                                        $aClasses[] = 'text-center';
                                    }

                                    if ($bIsBoolCell) {
                                        echo Helper::loadBoolCell($mValue);
                                    } elseif ($bIsUserCell) {
                                        echo Helper::loadUserCell($mValue);
                                    } else {
                                        echo Helper::loadCellAuto(
                                            $mValue,
                                            trim('field field--' . $sNormalisedLabel . ' ' . implode(' ', $aClasses))
                                        );
                                    }

                                } elseif (property_exists($oItem, $sProperty)) {

                                    $mValue          = $oItem->{$sProperty};
                                    $aClasses        = [];
                                    $sCellAdditional = '';
                                    if (classUses($CONFIG['MODEL_INSTANCE'], '\Nails\Common\Traits\Model\Nestable')) {
                                        $aBreadcrumbs = json_decode($oItem->breadcrumbs ?? '[]') ?? [];
                                        if (!empty($aBreadcrumbs) && $sProperty === $CONFIG['MODEL_INSTANCE']->getColumn('label')) {
                                            $mValue = '<span class="text-muted">╚</span>' . implode(
                                                    ' ',
                                                    [
                                                        str_repeat('<span class="text-muted">═</span>', count($aBreadcrumbs) - 1),
                                                        '&nbsp;',
                                                        $mValue,
                                                    ]
                                                );
                                        }
                                    }

                                    if ($bIsNumeric && !$bIsUserCell) {
                                        $mValue = number_format($mValue);
                                    }

                                    if ($bIsCenteredCell) {
                                        $aClasses[] = 'text-center';
                                    }

                                    if ($bIsBoolCell) {
                                        echo Helper::loadBoolCell($mValue);
                                    } elseif ($bIsUserCell) {
                                        echo Helper::loadUserCell($mValue);
                                    } else {
                                        echo Helper::loadCellAuto(
                                            $mValue,
                                            trim('field field--' . $sProperty . ' ' . implode(' ', $aClasses)),
                                            $sCellAdditional
                                        );
                                    }

                                } elseif (strpos($sProperty, '.') !== false) {

                                    //  @todo (Pablo - 2018-08-08) - Handle arrays in expanded objects
                                    $mValue   = ArrayHelper::dot((array) $oItem, $sProperty);
                                    $aClasses = [];

                                    if ($bIsNumeric && !$bIsUserCell) {
                                        $mValue = number_format($mValue);
                                    }

                                    if ($bIsCenteredCell) {
                                        $aClasses[] = 'text-center';
                                    }

                                    if ($bIsBoolCell) {
                                        echo Helper::loadBoolCell($mValue);
                                    } elseif ($bIsUserCell) {
                                        echo Helper::loadUserCell($mValue);
                                    } else {
                                        echo Helper::loadCellAuto(
                                            $mValue ?? '<span class="text-muted">&mdash;</span>',
                                            trim('field field--' . $sProperty . ' ' . implode(' ', $aClasses))
                                        );
                                    }

                                } else {
                                    ?>
                                    <td class="field field--<?=$sProperty?>">
                                        <span class="text-muted">&mdash;</span>
                                    </td>
                                    <?php
                                }
                            }

                            //  So that the "no actions" text shows when cell is empty
                            echo '<td class="actions">';
                            foreach ($CONFIG['INDEX_ROW_BUTTONS'] as $aButton) {

                                $cEnabled = getFromArray('enabled', $aButton);
                                if (is_object($cEnabled) && ($cEnabled instanceof \Closure) && !$cEnabled($oItem)) {
                                    continue;
                                }

                                $sUrl = getFromArray('url', $aButton);
                                if (is_object($sUrl) && ($sUrl instanceof \Closure)) {
                                    $sUrl = $sUrl($oItem);
                                }

                                $sLabel = getFromArray('label', $aButton);
                                if (is_object($sLabel) && ($sLabel instanceof \Closure)) {
                                    $sLabel = $sLabel($oItem);
                                }

                                $sClass = getFromArray('class', $aButton);
                                if (is_object($sClass) && ($sClass instanceof \Closure)) {
                                    $sClass = $sClass($oItem);
                                }

                                $sAttr = getFromArray('attr', $aButton);
                                if (is_object($sAttr) && ($sAttr instanceof \Closure)) {
                                    $sAttr = $sAttr($oItem);
                                }

                                $sPerm = getFromArray('permission', $aButton);
                                if (is_object($sPerm) && ($sPerm instanceof \Closure)) {
                                    $sPerm = $sPerm($oItem);
                                }

                                $sPerm = $sPerm ? 'admin:' . $CONFIG['PERMISSION'] . ':' . $sPerm : '';

                                if (empty($CONFIG['PERMISSION']) || empty($sPerm) || userHasPermission($sPerm)) {

                                    $sLabel = $oMustache->render($sLabel, $oItem);
                                    $sClass = $oMustache->render($sClass, $oItem);
                                    $sAttr  = $oMustache->render($sAttr, $oItem);

                                    if (is_array($sUrl)) {
                                        ?>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs dropdown-toggle <?=$sClass?>" <?=$sAttr?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <?=$sLabel?> <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php
                                                foreach ($sUrl as $sLabel => $sItemUrl) {
                                                    $sItemUrl = $oMustache->render($sItemUrl, $oItem);
                                                    if (!preg_match('/^(\/|https?:\/\/)/', $sItemUrl)) {
                                                        $sItemUrl = $CONFIG['BASE_URL'] . '/' . $sItemUrl;
                                                    }
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

                                        $sUrl = $oMustache->render($sUrl, $oItem);
                                        if (!preg_match('/^(\/|https?:\/\/)/', $sUrl)) {
                                            $sUrl = $CONFIG['BASE_URL'] . '/' . $sUrl;
                                        }

                                        echo anchor(
                                            $sUrl,
                                            $sLabel,
                                            'class="btn btn-xs ' . $sClass . '" ' . $sAttr
                                        );
                                    }
                                }
                            }
                            echo '</td>';
                            ?>
                        </tr>
                        <?php
                    }

                } else {
                    ?>
                    <tr>
                        <td colspan="<?=count($CONFIG['INDEX_FIELDS']) + 1?>" class="no-data">
                            No items found
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?=adminHelper('loadPagination', $pagination)?>
</div>
