<?php

namespace Tests\Admin\Service;

use Nails\Admin\Factory\Breadcrumb as BreadcrumbFactory;
use Nails\Admin\Service\Breadcrumb;
use PHPUnit\Framework\TestCase;

class BreadcrumbTest extends TestCase
{
    public function testStartsEmpty(): void
    {
        $oTrail = new Breadcrumb();

        $this->assertTrue($oTrail->isEmpty());
        $this->assertSame([], $oTrail->getItems());
        $this->assertSame([], $oTrail->getLabels());
    }

    // --------------------------------------------------------------------------

    public function testAddAppendsCrumbsFromLabels(): void
    {
        $oTrail = (new Breadcrumb())
            ->add('Users', '/users')
            ->add('Create', '/create');

        $this->assertFalse($oTrail->isEmpty());
        $this->assertCount(2, $oTrail->getItems());
        $this->assertSame(['Users', 'Create'], $oTrail->getLabels());
        $this->assertSame('/users', $oTrail->getItems()[0]->getUrl());
        $this->assertSame('/create', $oTrail->getItems()[1]->getUrl());
    }

    // --------------------------------------------------------------------------

    public function testAddAcceptsCrumbInstances(): void
    {
        $oCrumb = new BreadcrumbFactory('Import', '/import');
        $oTrail = (new Breadcrumb())
            ->add('Users')
            ->add($oCrumb);

        $this->assertSame(['Users', 'Import'], $oTrail->getLabels());
        $this->assertSame($oCrumb, $oTrail->getItems()[1]);
    }

    // --------------------------------------------------------------------------

    public function testPrependInsertsAtTheStart(): void
    {
        $oTrail = (new Breadcrumb())
            ->add('Users', '/users')
            ->prepend('Admin', '/admin');

        $this->assertSame(['Admin', 'Users'], $oTrail->getLabels());
        $this->assertSame('/admin', $oTrail->getItems()[0]->getUrl());
    }

    // --------------------------------------------------------------------------

    public function testPrependAcceptsCrumbInstances(): void
    {
        $oTrail = (new Breadcrumb())
            ->add('Users')
            ->prepend(new BreadcrumbFactory('Admin', '/admin'));

        $this->assertSame('Admin', $oTrail->getItems()[0]->getLabel());
        $this->assertTrue($oTrail->getItems()[0]->hasUrl());
    }

    // --------------------------------------------------------------------------

    public function testResetClearsTheTrail(): void
    {
        $oTrail = (new Breadcrumb())
            ->add('Users')
            ->reset();

        $this->assertTrue($oTrail->isEmpty());
        $this->assertSame([], $oTrail->getItems());
        $this->assertSame([], $oTrail->getLabels());
    }

    // --------------------------------------------------------------------------

    public function testAddAndPrependAreChainable(): void
    {
        $oTrail = new Breadcrumb();

        $this->assertSame($oTrail, $oTrail->add('Users'));
        $this->assertSame($oTrail, $oTrail->prepend('Admin'));
        $this->assertSame($oTrail, $oTrail->reset());
    }
}
