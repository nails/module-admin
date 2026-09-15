<?php

namespace Tests\Admin\Stub;

use Nails\Admin\Event\Listener\Export\Deleted;
use Nails\Admin\Model\Export;
use Nails\Cdn\Service\Cdn;
use Nails\Common\Service\Logger;

/**
 * Skips Factory in the production constructor so execute() can be exercised
 * with spies.
 */
class ExportDeletedListener extends Deleted
{
    public function __construct(
        private readonly Cdn $oCdn,
        private readonly Logger $oLogger,
    ) {
        $this
            ->setEvent(Export::EVENT_DELETED)
            ->setCallback([$this, 'execute']);
    }

    protected function cdn(): Cdn
    {
        return $this->oCdn;
    }

    protected function logger(): Logger
    {
        return $this->oLogger;
    }
}
