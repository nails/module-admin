<?php

namespace Tests\Admin\Stub;

use Nails\Cdn\Resource;
use Nails\Cdn\Service\Cdn;

/**
 * Records objectDestroy() calls and can be told to fail or throw.
 */
class CdnSpy extends Cdn
{
    /**
     * @var array<int, int|string|Resource\CdnObject|null>
     */
    public array $aDestroyed = [];

    /**
     * @var array<int, string>
     */
    public array $aFailures = [];

    /**
     * @var array<int, string>
     */
    public array $aThrows = [];

    public function __construct()
    {
        // Deliberately does not stand up a driver
    }

    public function objectDestroy(int|string|Resource\CdnObject|null $object): bool
    {
        $this->aDestroyed[] = $object;

        if (array_key_exists((int) $object, $this->aThrows)) {
            throw new \RuntimeException($this->aThrows[(int) $object]);
        }

        if (array_key_exists((int) $object, $this->aFailures)) {
            $this->setError($this->aFailures[(int) $object]);
            return false;
        }

        return true;
    }
}
