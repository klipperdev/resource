<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Functional\Domain;

use Doctrine\Persistence\ObjectRepository;
use Klipper\Component\Resource\Tests\Fixtures\Entity\Foo;

/**
 * Functional tests for Domain.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DomainTest extends AbstractDomainTest
{
    public function testMappingException(): void
    {
        $this->expectException(\Klipper\Component\Resource\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/The "([\\w\\\\\\/]+)" class is not managed by doctrine object manager/');

        $class = 'DateTime';

        $this->createDomain($class);
    }

    public function testGetRepository(): void
    {
        $domain = $this->createDomain();

        static::assertInstanceOf(ObjectRepository::class, $domain->getRepository());
    }

    public function testNewInstance(): void
    {
        $domain = $this->createDomain(Foo::class);
        $resource1 = $domain->newInstance();
        $resource2 = $this->objectFactory->create(Foo::class);

        static::assertEquals($resource2, $resource1);
    }
}
