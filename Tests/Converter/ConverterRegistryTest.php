<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Converter;

use Klipper\Component\Resource\Converter\ConverterRegistry;
use Klipper\Component\Resource\Converter\ConverterRegistryInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for converter registry.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ConverterRegistryTest extends TestCase
{
    protected ?ConverterRegistryInterface $registry = null;

    protected function setUp(): void
    {
        $converter = $this->getMockBuilder('Klipper\Component\Resource\Converter\ConverterInterface')->getMock();
        $converter->expects(static::any())
            ->method('getName')
            ->willReturn('foo')
        ;

        $this->registry = new ConverterRegistry([
            $converter,
        ]);
    }

    public function testUnexpectedTypeException(): void
    {
        $this->expectException(\Klipper\Component\Resource\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Klipper\\Component\\Resource\\Converter\\ConverterInterface", "DateTime" given');

        new ConverterRegistry([
            new \DateTime(),
        ]);
    }

    public function testHas(): void
    {
        static::assertTrue($this->registry->has('foo'));
        static::assertFalse($this->registry->has('bar'));
    }

    public function testGetNonExistentConverter(): void
    {
        $this->expectException(\Klipper\Component\Resource\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Could not load content converter "(\\w+)"/');

        $this->registry->get('bar');
    }

    public function testGet(): void
    {
        $converter = $this->registry->get('foo');

        static::assertInstanceOf('Klipper\Component\Resource\Converter\ConverterInterface', $converter);
    }
}
