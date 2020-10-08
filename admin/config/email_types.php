<?php

/**
 * This config file defines email types for this module.
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Config
 * @author      Nails Dev Team
 * @link
 */

$config['email_types'] = [
    (object) [
        'slug'            => 'data_export',
        'name'            => 'Admin: Data Export Success',
        'can_unsubscribe' => false,
        'description'     => 'Sent when a data export is completed successfully',
        'template_header' => '',
        'template_body'   => 'admin/Email/templates/data_export/success',
        'template_footer' => '',
        'default_subject' => 'Data Export Complete',
        'can_unsubscribe' => true,
        'factory'         => 'nails/module-admin::EmailDataExportSuccess',
    ],
    (object) [
        'slug'            => 'data_export_fail',
        'name'            => 'Admin: Data Export Success',
        'can_unsubscribe' => false,
        'description'     => 'Sent when a data export fails',
        'template_header' => '',
        'template_body'   => 'admin/Email/templates/data_export/fail',
        'template_footer' => '',
        'default_subject' => 'Data Export Failed',
        'can_unsubscribe' => true,
        'factory'         => 'nails/module-admin::EmailDataExportFail',
    ],
];
