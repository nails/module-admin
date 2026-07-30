<?php

namespace Nails\Admin\Factory\Email\DataExport;

use Nails\Email\Interfaces;
use Nails\Email\Traits;

class Success implements Interfaces\Email
{
    use Traits\Email;

    // --------------------------------------------------------------------------

    public function __construct()
    {
        $this->type('data_export');
    }

    /**
     * Returns test data to use when sending test emails
     *
     * @return array
     */
    public function getTestData(): array
    {
        return [
            'login_url' => 'https://www.example.com',
            'source'    => [
                'label'       => 'The source\'s label',
                'description' => 'The source\'s description',
            ],
            'format'    => [
                'label'       => 'The format\'s label',
                'description' => 'The format\'s description',
            ],
        ];
    }
}
