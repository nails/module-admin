<?php

use Nails\Admin\Helper;
use Nails\Admin\Resource\DataExport\Format;
use Nails\Admin\Resource\DataExport\Source;
use Nails\Admin\Resource\Export;

/**
 * @var Source[] $aSources
 * @var Format[] $aFormats
 * @var Export   $aRecent
 * @var string   $sDefaultFormat
 * @var int      $iRetentionPeriod
 * @var int      $iUrlTtl
 */

?>
<div class="group-utilities export">
    <?=form_open(null, 'id="export-form"')?>
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
            'info'     => implode(
                PHP_EOL,
                array_filter(
                    array_map(
                        function (Source $oSource) {
                            $sDescriptionExtended = $oSource->description_extended;
                            return $sDescriptionExtended
                                ? sprintf(
                                    '<div class="alert alert-info" data-revealer="data-export" data-reveal-on="%s">%s</div>',
                                    $oSource->slug,
                                    $sDescriptionExtended
                                )
                                : null;
                        },
                        $aSources
                    )
                )
            ),
        ];

        $aOptions = [];
        foreach ($aSources as $oSource) {
            $aField['options'][$oSource->slug] = sprintf(
                '%s &mdash; %s',
                $oSource->label,
                $oSource->description
            );
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
    <?php

    echo Helper::floatingControls([
        'save' => [
            'text' => 'Export',
        ],
    ]);

    ?>
    <?=form_close()?>
    <div id="export-recent-container" class="hidden">
        <hr>
        <h2>Recent exports</h2>
        <table class="table table-striped table-hover table-bordered table-responsive">
            <thead class="table-dark">
                <tr>
                    <th class="export-source">Export</th>
                    <th class="export-options">Options</th>
                    <th class="export-format">Format</th>
                    <th class="export-status">Status</th>
                    <th class="export-requested">Requested</th>
                    <th class="export-expires">Expires</th>
                    <th class="export-generated">Generated</th>
                    <th class="export-actions actions">Actions</th>
                </tr>
            </thead>
            <tbody id="export-recent" class="align-middle">
            </tbody>
        </table>
    </div>
</div>
