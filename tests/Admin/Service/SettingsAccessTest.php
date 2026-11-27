<?php

namespace Tests\Admin\Service;

use Nails\Admin\Admin\Permission\Settings\Manage;
use Nails\Admin\Admin\Permission\SuperUser;
use Nails\Admin\Service\Permission;
use Nails\Auth\Resource\User;
use PHPUnit\Framework\TestCase;

class SettingsAccessTest extends TestCase
{
    const PAGE_PERMISSION = 'Tests\\Admin\\Permission\\Settings\\Dummy';

    // --------------------------------------------------------------------------

    /**
     * @param string[] $aAcl
     */
    protected function getService(array $aAcl): Permission
    {
        $oService = new class extends Permission {

            public array $aTestAcl = [];

            public function __construct()
            {
            }

            public function userHasPermission($mPermission, ?User $oUser = null): bool
            {
                if (in_array(SuperUser::class, $this->aTestAcl, true)) {
                    return true;
                }

                return in_array($mPermission, $this->aTestAcl, true);
            }
        };

        $oService->aTestAcl = $aAcl;

        return $oService;
    }

    // --------------------------------------------------------------------------

    public function testSudoPermissionGrantsAccess(): void
    {
        $oService = $this->getService([Manage::class]);

        $this->assertTrue($oService->userCanAccessSettings(static::PAGE_PERMISSION));
    }

    // --------------------------------------------------------------------------

    public function testPagePermissionGrantsAccess(): void
    {
        $oService = $this->getService([static::PAGE_PERMISSION]);

        $this->assertTrue($oService->userCanAccessSettings(static::PAGE_PERMISSION));
    }

    // --------------------------------------------------------------------------

    public function testNeitherPermissionDeniesAccess(): void
    {
        $oService = $this->getService([]);

        $this->assertFalse($oService->userCanAccessSettings(static::PAGE_PERMISSION));
    }

    // --------------------------------------------------------------------------

    public function testSuperUserGrantsAccess(): void
    {
        $oService = $this->getService([SuperUser::class]);

        $this->assertTrue($oService->userCanAccessSettings(static::PAGE_PERMISSION));
    }
}
