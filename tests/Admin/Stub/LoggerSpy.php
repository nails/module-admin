<?php

namespace Tests\Admin\Stub;

use Nails\Common\Service\Logger;

class LoggerSpy extends Logger
{
    /**
     * @var string[]
     */
    public array $aErrors = [];

    public function __construct()
    {
        // Deliberately does not stand up a log file
    }

    public function error($sLine = ''): self
    {
        $this->aErrors[] = (string) $sLine;
        return $this;
    }
}
