<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Object;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Klipper\Component\Resource\Object\DoctrineObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for Doctrine object factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DoctrineObjectFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $of = new DoctrineObjectFactory($em);

        $em->expects(static::once())
            ->method('getClassMetadata')
            ->with(\stdClass::class)
            ->willReturn(new ClassMetadata(\stdClass::class))
        ;

        $val = $of->create(\stdClass::class);

        static::assertInstanceOf(\stdClass::class, $val);
    }
}
