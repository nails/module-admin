<?php

namespace Tests\Admin\Service;

use Nails\Admin\Service\Permission;
use PHPUnit\Framework\TestCase;

class PermissionIpWhitelistTest extends TestCase
{
    /**
     * Returns a Permission service with a fixed whitelist, skipping permission discovery
     *
     * @param string[] $aWhitelist
     *
     * @return Permission
     */
    protected function getService(array $aWhitelist): Permission
    {
        $oService = new class extends Permission {

            public array $aTestWhitelist = [];

            public function __construct()
            {
            }

            public function getIpWhitelist(): array
            {
                return $this->aTestWhitelist;
            }
        };

        $oService->aTestWhitelist = $aWhitelist;

        return $oService;
    }

    // --------------------------------------------------------------------------

    public function testEmptyWhitelistAllowsEveryIp(): void
    {
        $oService = $this->getService([]);

        $this->assertTrue($oService->isIpAllowed('203.0.113.7'));
        $this->assertTrue($oService->isIpAllowed('2001:db8::1'));
    }

    // --------------------------------------------------------------------------

    public function testExactIpIsAllowed(): void
    {
        $oService = $this->getService(['203.0.113.7']);

        $this->assertTrue($oService->isIpAllowed('203.0.113.7'));
        $this->assertFalse($oService->isIpAllowed('203.0.113.8'));
    }

    // --------------------------------------------------------------------------

    public function testCidrRangeIsAllowed(): void
    {
        $oService = $this->getService(['198.51.100.0/24']);

        $this->assertTrue($oService->isIpAllowed('198.51.100.1'));
        $this->assertTrue($oService->isIpAllowed('198.51.100.254'));
        $this->assertFalse($oService->isIpAllowed('198.51.101.1'));
    }

    // --------------------------------------------------------------------------

    public function testAnyMatchingEntryAllows(): void
    {
        $oService = $this->getService(['203.0.113.7', '198.51.100.0/24']);

        $this->assertTrue($oService->isIpAllowed('203.0.113.7'));
        $this->assertTrue($oService->isIpAllowed('198.51.100.50'));
        $this->assertFalse($oService->isIpAllowed('192.0.2.1'));
    }
}
