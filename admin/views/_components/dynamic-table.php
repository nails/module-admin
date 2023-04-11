<?php

/**
 * @var string  $sKey
 * @var array[] $aFields
 * @var array   $aData
 * @var bool    $bIsSortable
 */

$aData      = htmlentities(json_encode($aData));
$sBodyClass = implode(' ', array_filter([
    'js-admin-dynamic-table__body',
    $bIsSortable ? 'js-admin-sortable' : null,
]))

?>
<table class="js-admin-dynamic-table table table-striped table-hover table-bordered table-responsive" data-data="<?=$aData?>">
    <thead class="table-dark">
        <tr>
            <?php
            if ($bIsSortable) {
                ?>
                <th width="40">Order</th>
                <?php
            }

            foreach ($aFields as $sLabel => $aField) {
                $bIsHidden = getFromArray('type', $aField) === 'hidden';
                if (!$bIsHidden) {
                    echo '<th>' . getFromArray('label', $aField, $sLabel) . '</th>';
                }
            }

            ?>
            <th width="40"></th>
        </tr>
    </thead>
    <tbody class="<?=$sBodyClass?>" data-handle=".handle"></tbody>
    <script type="text/x-template" class="js-admin-dynamic-table__template">
    <tr>
        <?php
        if ($bIsSortable) {
            ?>
            <td class="text-center handle">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </td>
            <?php
        }

        foreach ($aFields as $aField) {

            $sFieldKey = getFromArray('key', $aField);
            $sType     = getFromArray('type', $aField, 'text');
            $sClass    = getFromArray('class', $aField);

            $sCompiledKey = $sKey . '[{{index}}][' . $sFieldKey . ']';
            if ($sFieldKey === 'order') {
                $sClass .= ' js-admin-sortable__order';
            }

            if (strtolower($sType) === 'hidden') {
                echo form_hidden($sCompiledKey, '{{' . $sFieldKey . '}}');

            } elseif (strtolower($sType) === 'dropdown') {
                echo '<td>';
                echo call_user_func_array(
                    'form_dropdown',
                    [
                        $sCompiledKey,
                        getFromArray('options', $aField, []),
                        null,
                        [
                            'data-dynamic-table-value' => '{{' . $sFieldKey . '}}',
                        ],
                    ]
                );
                echo '</td>';

            } elseif (is_callable('form_' . $sType)) {
                echo '<td>';
                echo call_user_func_array('form_' . $sType, [$sCompiledKey, '{{' . $sFieldKey . '}}']);
                echo '</td>';

            } elseif (!empty($aField['html'])) {
                echo '<td>';
                echo $aField['html'];
                echo '</td>';
            }
        }

        ?>
        <td>
            <button type="button" class="btn btn-xs btn-danger js-admin-dynamic-table__remove">
                &times;
            </button>
        </td>
    </tr>
    </script>
    <tbody>
        <tr>
            <td colspan="5">
                <button type="button" class="btn btn-xs btn-success js-admin-dynamic-table__add">
                    &plus; Add
                </button>
            </td>
        </tr>
    </tbody>
</table>
