<?php

namespace Tests\Admin\Event\Listener\Export;

use Nails\Admin\Model\Export;
use Nails\Admin\Resource\Export as ExportResource;
use PHPUnit\Framework\TestCase;
use Tests\Admin\Stub\CdnSpy;
use Tests\Admin\Stub\ExportDeletedListener;
use Tests\Admin\Stub\LoggerSpy;

/**
 * @covers \Nails\Admin\Event\Listener\Export\Deleted
 */
class DeletedTest extends TestCase
{
    public function test_a_missing_download_is_ignored(): void
    {
        $oCdn    = new CdnSpy();
        $oLogger = new LoggerSpy();
        $oModel  = $this->createMock(Export::class);
        $oModel->expects($this->never())->method('countAll');

        $this->listener($oCdn, $oLogger)->execute(7, $this->export(null), $oModel);

        $this->assertSame([], $oCdn->aDestroyed);
        $this->assertSame([], $oLogger->aErrors);
    }

    // --------------------------------------------------------------------------

    public function test_a_null_item_is_ignored(): void
    {
        $oCdn    = new CdnSpy();
        $oLogger = new LoggerSpy();
        $oModel  = $this->createMock(Export::class);
        $oModel->expects($this->never())->method('countAll');

        $this->listener($oCdn, $oLogger)->execute(7, null, $oModel);

        $this->assertSame([], $oCdn->aDestroyed);
        $this->assertSame([], $oLogger->aErrors);
    }

    // --------------------------------------------------------------------------

    public function test_a_shared_download_is_left_alone(): void
    {
        $oCdn    = new CdnSpy();
        $oLogger = new LoggerSpy();
        $oModel  = $this->createMock(Export::class);
        $oModel
            ->expects($this->once())
            ->method('countAll')
            ->with(['where' => [['download_id', 99]]])
            ->willReturn(1);

        $this->listener($oCdn, $oLogger)->execute(7, $this->export(99), $oModel);

        $this->assertSame([], $oCdn->aDestroyed);
        $this->assertSame([], $oLogger->aErrors);
    }

    // --------------------------------------------------------------------------

    public function test_an_orphaned_download_is_destroyed(): void
    {
        $oCdn    = new CdnSpy();
        $oLogger = new LoggerSpy();
        $oModel  = $this->createMock(Export::class);
        $oModel
            ->expects($this->once())
            ->method('countAll')
            ->willReturn(0);

        $this->listener($oCdn, $oLogger)->execute(7, $this->export(99), $oModel);

        $this->assertSame([99], $oCdn->aDestroyed);
        $this->assertSame([], $oLogger->aErrors);
    }

    // --------------------------------------------------------------------------

    public function test_a_false_return_is_logged_and_not_thrown(): void
    {
        $oCdn            = new CdnSpy();
        $oCdn->aFailures = [99 => 'Nothing to destroy.'];
        $oLogger         = new LoggerSpy();
        $oModel          = $this->createStub(Export::class);
        $oModel->method('countAll')->willReturn(0);

        $this->listener($oCdn, $oLogger)->execute(7, $this->export(99), $oModel);

        $this->assertSame([99], $oCdn->aDestroyed);
        $this->assertSame(
            ['Failed to destroy CDN object #99 belonging to admin export #7; Nothing to destroy.'],
            $oLogger->aErrors
        );
    }

    // --------------------------------------------------------------------------

    public function test_a_throw_is_logged_and_not_rethrown(): void
    {
        $oCdn          = new CdnSpy();
        $oCdn->aThrows = [99 => 'The driver fell over'];
        $oLogger       = new LoggerSpy();
        $oModel        = $this->createStub(Export::class);
        $oModel->method('countAll')->willReturn(0);

        $this->listener($oCdn, $oLogger)->execute(7, $this->export(99), $oModel);

        $this->assertSame([99], $oCdn->aDestroyed);
        $this->assertSame(
            ['Failed to destroy CDN object #99 belonging to admin export #7; The driver fell over'],
            $oLogger->aErrors
        );
    }

    // --------------------------------------------------------------------------

    public function test_a_count_failure_is_logged_and_not_rethrown(): void
    {
        $oCdn    = new CdnSpy();
        $oLogger = new LoggerSpy();
        $oModel  = $this->createStub(Export::class);
        $oModel
            ->method('countAll')
            ->willThrowException(new \RuntimeException('The database fell over'));

        $this->listener($oCdn, $oLogger)->execute(7, $this->export(99), $oModel);

        $this->assertSame([], $oCdn->aDestroyed);
        $this->assertSame(
            ['Failed to destroy CDN object #99 belonging to admin export #7; The database fell over'],
            $oLogger->aErrors
        );
    }

    // --------------------------------------------------------------------------

    private function listener(CdnSpy $oCdn, LoggerSpy $oLogger): ExportDeletedListener
    {
        return new ExportDeletedListener($oCdn, $oLogger);
    }

    private function export(?int $iDownloadId): ExportResource
    {
        return new ExportResource((object) [
            'id'          => 7,
            'download_id' => $iDownloadId,
        ]);
    }
}
