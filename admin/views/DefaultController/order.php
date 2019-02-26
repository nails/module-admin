<?=form_open()?>
<table>
    <thead>
        <tr>
            <th width="50"></th>
            <th>Item</th>
        </tr>
    </thead>
    <tbody class="js-admin-sortable" data-handle=".handle">
        <?php
        foreach ($items as $oItem) {
            ?>
            <tr>
                <td width="50" class="text-center handle">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </td>
                <td>
                    <?php

                    if (is_callable($CONFIG['SORT_LABEL'])) {
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
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<div class="admin-floating-controls">
    <button type="submit" class="btn btn-primary">
        Save Changes
    </button>
</div>
<?=form_close()?>
