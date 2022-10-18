<?php

?>
<div class="group-utilities export">
    <?=form_open()?>
    <fieldset>
        <legend>Data Source</legend>
        <?php

        $aField = [
            'key'      => 'source',
            'label'    => 'Source',
            'required' => true,
            'class'    => 'select2',
            'options'  => [],
            'data'     => [
                'revealer' => 'data-export',
            ],
        ];

        $aOptions = [];
        foreach ($aSources as $oSource) {
            $aField['options'][$oSource->slug] = $oSource->label . ' - ' . $oSource->description;
        }

        echo form_field_dropdown($aField);

        ?>
    </fieldset>
    <?php

    foreach ($aSources as $oSource) {
        if (empty($oSource->options)) {
            continue;
        }
        ?>
        <fieldset data-revealer="data-export" data-reveal-on="<?=$oSource->slug?>">
            <legend>Options</legend>
            <?php

            foreach ($oSource->options as $aOption) {
                $aOption['key'] = 'options[' . $oSource->slug . '][' . getFromArray('key', $aOption) . ']';
                if (!empty($aOption['type']) && is_callable('form_field_' . $aOption['type'])) {
                    echo call_user_func('form_field_' . $aOption['type'], $aOption);
                } else {
                    echo form_field($aOption);
                }
            }
            ?>
        </fieldset>
        <?php
    }

    ?>
    <fieldset>
        <legend>Export Format</legend>
        <?php

        $aField = [
            'key'      => 'format',
            'label'    => 'Format',
            'required' => true,
            'class'    => 'select2',
            'default'  => $sDefaultFormat,
            'options'  => [],
        ];

        $aOptions = [];
        foreach ($aFormats as $oFormat) {
            $aField['options'][$oFormat->slug] = $oFormat->label . ' - ' . $oFormat->description;
        }

        echo form_field_dropdown($aField);

        ?>
    </fieldset>
    <p>
        <?=form_submit('submit', 'Export', 'class="btn btn-primary"')?>
    </p>
    <?=form_close()?>
    <hr>
    <h2>Recent exports</h2>
    <?php
    if ($iRetentionPeriod) {
        ?>
        <p class="alert alert-info">
            Reports are automatically removed after <?=floor($iRetentionPeriod / 60)?> minutes.
        </p>
        <?php
    }
    ?>
    <table class="table table-striped table-hover table-bordered table-responsive">
        <thead class="table-dark">
            <tr>
                <th>Export</th>
                <th>Options</th>
                <th width="100">Format</th>
                <th>Status</th>
                <th width="150">Requested</th>
                <th width="150">Generated</th>
                <th class="actions" width="100">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php

            if (!empty($aRecent)) {
                foreach ($aRecent as $oItem) {
                    ?>
                    <tr>
                        <td><?=$oItem->source?></td>
                        <td><?=$oItem->options?></td>
                        <td><?=$oItem->format?></td>
                        <td>
                            <?=$oItem->status?>
                            <?=$oItem->status === 'FAILED' ? '<small>' . $oItem->error . '</small>' : ''?>
                        </td>
                        <?=Nails\Admin\Helper::loadDateCell($oItem->created)?>
                        <?=Nails\Admin\Helper::loadDateCell($oItem->status === 'COMPLETE' ? $oItem->modified : '', '&mdash;')?>
                        <td class="actions">
                            <?php

                            if ($oItem->download_id) {
                                echo anchor(
                                    cdnExpiringUrl($oItem->download_id, $iUrlTtl, true),
                                    'Download',
                                    'class="btn btn-xs btn-primary"'
                                );
                            }

                            ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" class="no-data">
                        You have not generated any reports
                    </td>
                </tr>
                <?php
            }

            ?>
        </tbody>
    </table>
</div>
