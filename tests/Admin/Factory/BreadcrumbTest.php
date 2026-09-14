<?php

namespace Tests\Admin\Factory;

use Nails\Admin\Factory\Breadcrumb;
use PHPUnit\Framework\TestCase;

class BreadcrumbTest extends TestCase
{
    public function testDefaultsToEmptyLabelAndNoUrl(): void
    {
        $oCrumb = new Breadcrumb();

        $this->assertSame('', $oCrumb->getLabel());
        $this->assertNull($oCrumb->getUrl());
        $this->assertFalse($oCrumb->hasUrl());
    }

    // --------------------------------------------------------------------------

    public function testConstructorSetsLabelAndUrl(): void
    {
        $oCrumb = new Breadcrumb('Users', '/admin/auth/accounts');

        $this->assertSame('Users', $oCrumb->getLabel());
        $this->assertSame('/admin/auth/accounts', $oCrumb->getUrl());
        $this->assertTrue($oCrumb->hasUrl());
    }

    // --------------------------------------------------------------------------

    public function testSettersAreChainable(): void
    {
        $oCrumb = new Breadcrumb();

        $this->assertSame($oCrumb, $oCrumb->setLabel('Create'));
        $this->assertSame($oCrumb, $oCrumb->setUrl('/create'));
        $this->assertSame('Create', $oCrumb->getLabel());
        $this->assertSame('/create', $oCrumb->getUrl());
    }

    // --------------------------------------------------------------------------

    public function testEmptyUrlIsTreatedAsNoLink(): void
    {
        $oCrumb = new Breadcrumb('Users', '');

        $this->assertFalse($oCrumb->hasUrl());
        $this->assertNull($oCrumb->getUrl());
    }

    // --------------------------------------------------------------------------

    public function testSetUrlNullClearsTheLink(): void
    {
        $oCrumb = (new Breadcrumb('Users', '/users'))
            ->setUrl(null);

        $this->assertFalse($oCrumb->hasUrl());
        $this->assertNull($oCrumb->getUrl());
    }
}
