<?php

namespace Nails\Admin\Factory\Email\DataExport;

use Nails\Email\Interfaces;
use Nails\Email\Traits;

class Fail implements Interfaces\Email
{
    use Traits\Email;

    // --------------------------------------------------------------------------

    public function __construct()
    {
        $this->type('data_export_fail');
    }

    /**
     * Returns test data to use when sending test emails
     *
     * @return array
     */
    public function getTestData(): array
    {
        return [
            'error' => 'The error reason for the failure.',
        ];
    }
}
