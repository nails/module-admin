<?php

styleguideExample(null, static function () {
    echo form_open();
    ?>
    <fieldset>
        <legend>Record</legend>
        <?php
        echo form_field([
            'key'        => 'sg-forms-title',
            'label'      => 'Title',
            'default'    => 'Quarterly report',
            'required'   => true,
            'sub_label'  => 'Shown in lists and search',
        ]);
        echo form_field_email([
            'key'     => 'sg-forms-email',
            'label'   => 'Email',
            'default' => 'alex@example.com',
            'tip'     => 'Used for notifications',
        ]);
        echo form_field_url([
            'key'     => 'sg-forms-url',
            'label'   => 'URL',
            'default' => 'https://example.com/reports/quarterly',
        ]);
        echo form_field_textarea([
            'key'     => 'sg-forms-summary',
            'label'   => 'Summary',
            'default' => 'A short summary of the item.',
        ]);
        echo form_field_dropdown([
            'key'     => 'sg-forms-status',
            'label'   => 'Status',
            'default' => 'draft',
            'options' => [
                'draft'     => 'Draft',
                'review'    => 'In review',
                'published' => 'Published',
            ],
        ]);
        echo form_field_boolean([
            'key'     => 'sg-forms-featured',
            'label'   => 'Featured',
            'default' => false,
        ]);
        echo form_field_boolean([
            'key'      => 'sg-forms-alignment',
            'label'    => 'Alignment',
            'default'  => true,
            'text_on'  => 'Wide',
            'text_off' => 'Narrow',
        ]);
        echo form_field_boolean([
            'key'      => 'sg-forms-published',
            'label'    => 'Published',
            'default'  => true,
            'readonly' => true,
        ]);
        echo form_field_boolean([
            'key'      => 'sg-forms-temp-password',
            'label'    => 'Temporary password',
            'default'  => false,
            'text_on'  => 'Yes',
            'text_off' => 'No',
            'info'     => 'Require password update on next log in',
        ]);
        echo form_field_checkbox([
            'key'     => 'sg-forms-notify[]',
            'label'   => 'Notify',
            'options' => [
                [
                    'label'    => 'Editors',
                    'value'    => 'editors',
                    'selected' => true,
                ],
                [
                    'label' => 'Owners',
                    'value' => 'owners',
                ],
            ],
        ]);
        echo form_field_radio([
            'key'     => 'sg-forms-visibility',
            'label'   => 'Visibility',
            'options' => [
                [
                    'label'    => 'Public',
                    'value'    => 'public',
                    'selected' => true,
                ],
                [
                    'label' => 'Private',
                    'value' => 'private',
                ],
            ],
        ]);
        echo form_field([
            'key'      => 'sg-forms-readonly',
            'label'    => 'Identifier',
            'default'  => 'REC-1042',
            'readonly' => true,
        ]);
        ?>
    </fieldset>
    <?php
    echo form_close();
}, ['flush' => true]);

styleguideExample('With an error', static function () {
    echo form_open();
    ?>
    <fieldset>
        <legend>Record</legend>
        <?php
        echo form_field([
            'key'     => 'sg-forms-error-title',
            'label'   => 'Title',
            'default' => '',
            'error'   => 'This field is required.',
        ]);
        echo form_field([
            'key'     => 'sg-forms-error-slug',
            'label'   => 'Slug',
            'default' => 'quarterly-report',
        ]);
        ?>
    </fieldset>
    <?php
    echo form_close();
}, ['flush' => true]);
