<?php

use Nails\Admin\Helper;
use Nails\Common\Service\View;
use Nails\Common\Traits\Model\Localised;
use Nails\Common\Traits\Model\Nestable;
use Nails\Factory;

$bIsLocalised = classUses($CONFIG['MODEL_INSTANCE'], Localised::class);
$bIsNestable  = classUses($CONFIG['MODEL_INSTANCE'], Nestable::class);

echo form_open();

/** @var \Nails\Admin\Factory\DefaultController\Sort\Section $oSection */
foreach ($aSections as $oSection) {
    if (count($aSections) > 1) {
        ?>
        <h2><?=$oSection->getLabel()?></h2>
        <?php
    }
    ?>
    <table class="table table-striped table-hover table-responsive table-sticky">
        <thead class="table-dark">
            <tr>
                <th width="50"></th>
                <?php
                if ($bIsLocalised) {
                    ?>
                    <th width="100" class="text-center">
                        Available In
                    </th>
                    <?php
                }
                ?>
                <th>Item</th>
                <?php
                foreach ($CONFIG['SORT_COLUMNS'] as $sLabel => $sProperty) {
                    ?>
                    <th>
                        <?=$sLabel?>
                    </th>
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tbody class="js-admin-sortable" data-handle=".handle">
            <?php
            foreach ($oSection->getItems() as $oItem) {
                ?>
                <tr>
                    <td width="50" class="text-center handle">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </td>
                    <?php
                    if ($bIsLocalised) {
                        ?>
                        <td width="100" class="text-center field--locale">
                            <?php
                            foreach ($oItem->available_locales as $oLocale) {
                                ?>
                                <span class="hint--bottom" aria-label="<?=$oLocale->getDisplayLanguage()?>">
                                <?=$oLocale->getFlagEmoji()?>
                            </span>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <td>
                        <?php

                        //  @todo (Pablo - 2019-12-09) - Support strict nestable sorting, i.e maintain parent/child
                        if ($bIsNestable) {

                            $sBreadcrumbsColumn = $CONFIG['MODEL_INSTANCE']->getBreadcrumbsColumn();
                            $aBreadcrumbs       = json_decode($oItem->breadcrumbs) ?? [];

                            if (!empty($aBreadcrumbs)) {

                                echo '<span class="text-muted">╚</span>';
                                echo str_repeat('<span class="text-muted">═</span>', count($aBreadcrumbs) - 1);
                                echo '&nbsp;';
                            }
                        }

                        if ($CONFIG['SORT_LABEL'] instanceof \Closure) {
                            echo call_user_func($CONFIG['SORT_LABEL'], $oItem);

                        } elseif (property_exists($oItem, $CONFIG['SORT_LABEL'])) {
                            echo $oItem->{$CONFIG['SORT_LABEL']};

                        } elseif (strpos($CONFIG['SORT_LABEL'], '.') !== false) {

                            //  @todo (Pablo - 2018-08-08) - Handle arrays in expanded objects
                            $aField     = explode('.', $CONFIG['SORT_LABEL']);
                            $aClasses   = [];
                            $sProperty1 = getFromArray(0, $aField);
                            $sProperty2 = getFromArray(1, $aField);

                            if (property_exists($oItem, $sProperty1)) {

                                if (!empty($oItem->{$sProperty1}) && property_exists($oItem->{$sProperty1}, $sProperty2)) {
                                    echo $oItem->{$sProperty1}->{$sProperty2};
                                } else {
                                    echo '<span class="text-muted">&mdash;</span>';
                                }
                            } else {
                                echo '<span class="text-muted">&mdash;</span>';
                            }

                        } else {
                            echo '<span class="text-muted">&mdash;</span>';
                        }

                        ?>
                        <input type="hidden" name="order[]" value="<?=$oItem->id?>">
                    </td>
                    <?php
                    foreach ($CONFIG['SORT_COLUMNS'] as $sLabel => $sProperty) {

                        echo '<td>';
                        if ($sProperty instanceof \Closure) {
                            echo call_user_func($sProperty, $oItem);

                        } elseif (property_exists($oItem, $sProperty)) {
                            echo $oItem->{$sProperty};

                        } elseif (strpos($sProperty, '.') !== false) {

                            //  @todo (Pablo - 2018-08-08) - Handle arrays in expanded objects
                            $aField     = explode('.', $sProperty);
                            $aClasses   = [];
                            $sProperty1 = getFromArray(0, $aField);
                            $sProperty2 = getFromArray(1, $aField);

                            if (property_exists($oItem, $sProperty1)) {

                                if (!empty($oItem->{$sProperty1}) && property_exists($oItem->{$sProperty1}, $sProperty2)) {
                                    echo $oItem->{$sProperty1}->{$sProperty2};
                                } else {
                                    echo '<span class="text-muted">&mdash;</span>';
                                }
                            } else {
                                echo '<span class="text-muted">&mdash;</span>';
                            }

                        } else {
                            echo '<span class="text-muted">&mdash;</span>';
                        }
                        echo '</td>';
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
}

echo Helper::floatingControls();
echo form_close();
