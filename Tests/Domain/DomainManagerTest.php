<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Domain;

use Klipper\Component\Resource\Domain\DomainFactoryInterface;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Domain\DomainManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for Domain Manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DomainManagerTest extends TestCase
{
    /**
     * @var DomainFactoryInterface|MockObject
     */
    protected $factory;

    /**
     * @var DomainInterface|MockObject
     */
    protected $domain;

    protected ?DomainManager $manager = null;

    protected function setUp(): void
    {
        $this->domain = $this->getMockBuilder(DomainInterface::class)->getMock();
        $this->factory = $this->getMockBuilder(DomainFactoryInterface::class)->getMock();

        $this->domain->expects(static::any())
            ->method('getClass')
            ->willReturn('Foo')
        ;

        $this->manager = new DomainManager($this->factory);
    }

    public function testHas(): void
    {
        $this->factory->expects(static::any())
            ->method('isManagedClass')
            ->willReturnCallback(static function ($value) {
                return 'Foo' === $value;
            })
        ;

        static::assertTrue($this->manager->has('Foo'));
        static::assertFalse($this->manager->has('Bar'));
    }

    public function testGet(): void
    {
        $this->factory->expects(static::once())
            ->method('isManagedClass')
            ->with('FooInterface')
            ->willReturn(true)
        ;

        static::assertTrue($this->manager->has('FooInterface'));

        $this->factory->expects(static::once())
            ->method('getManagedClass')
            ->with('FooInterface')
            ->willReturn('Foo')
        ;

        $this->factory->expects(static::once())
            ->method('create')
            ->with('Foo')
            ->willReturn($this->domain)
        ;

        static::assertSame($this->domain, $this->manager->get('FooInterface'));
        static::assertTrue($this->manager->has('Foo'));
    }

    public function testClear(): void
    {
        $this->factory->expects(static::once())
            ->method('clear')
        ;

        $this->manager->clear();
    }
}
