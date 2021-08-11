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

use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Event\PostUpdatesEvent;
use Klipper\Component\Resource\Event\PreUpdatesEvent;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Resource\ResourceListInterface;
use Klipper\Component\Resource\ResourceListStatutes;
use Klipper\Component\Resource\ResourceStatutes;
use Klipper\Component\Resource\Tests\Fixtures\Entity\Foo;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Functional tests for update methods of Domain.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DomainUpdateTest extends AbstractDomainTest
{
    public function getWrappedData(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdateWithErrorValidation(bool $wrapped): void
    {
        $domain = $this->createDomain();
        $foo = $this->insertResource($domain);
        $foo->setName(null);

        $this->runTestUpdateException($domain, $this->wrap($foo, $wrapped), '/This value should not be blank./');
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdateWithErrorDatabase(bool $wrapped): void
    {
        $domain = $this->createDomain();
        $foo = $this->insertResource($domain);
        $foo->setDetail(null);

        $this->runTestUpdateException($domain, $this->wrap($foo, $wrapped), $this->getIntegrityViolationMessage());
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdate(bool $wrapped): void
    {
        $domain = $this->createDomain();
        $foo = $this->insertResource($domain);
        $foo->setName('Foo');

        $preEvent = false;
        $postEvent = false;

        $this->dispatcher->addListener(PreUpdatesEvent::class, function (PreUpdatesEvent $e) use (&$preEvent, $domain): void {
            $preEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::PENDING, $resource->getStatus());
            }
        });
        $this->dispatcher->addListener(PostUpdatesEvent::class, function (PostUpdatesEvent $e) use (&$postEvent, $domain): void {
            $postEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::UPDATED, $resource->getStatus());
            }
        });

        static::assertCount(1, $domain->getRepository()->findAll());

        $resource = $domain->update($this->wrap($foo, $wrapped));
        static::assertCount(0, $resource->getErrors());
        static::assertSame('Foo', $resource->getRealData()->getName());

        static::assertTrue($preEvent);
        static::assertTrue($postEvent);

        static::assertCount(1, $domain->getRepository()->findAll());
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdatesWithErrorValidation(bool $wrapped): void
    {
        $domain = $this->createDomain();
        $objects = $this->insertResources($domain, 2);

        foreach ($objects as $object) {
            $object->setName(null);
        }

        $this->runTestUpdatesException($domain, $this->wrap($objects, $wrapped), '/This value should not be blank./', true);
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdatesWithErrorDatabase(bool $wrapped): void
    {
        $domain = $this->createDomain();
        $objects = $this->insertResources($domain, 2);

        foreach ($objects as $object) {
            $object->setDetail(null);
        }

        $this->runTestUpdatesException($domain, $this->wrap($objects, $wrapped), $this->getIntegrityViolationMessage(), false);
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdates(bool $wrapped): void
    {
        $this->runTestUpdates(false, $wrapped);
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdatesAutoCommitWithErrorValidationAndErrorDatabase(bool $wrapped): void
    {
        $domain = $this->createDomain();
        $objects = $this->insertResources($domain, 2);

        $objects[0]->setName(null);
        $objects[1]->setDetail(null);

        $preEvent = false;
        $postEvent = false;

        $this->dispatcher->addListener(PreUpdatesEvent::class, function (PreUpdatesEvent $e) use (&$preEvent, $domain): void {
            $preEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::PENDING, $resource->getStatus());
            }
        });
        $this->dispatcher->addListener(PostUpdatesEvent::class, function (PostUpdatesEvent $e) use (&$postEvent, $domain): void {
            $postEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::ERROR, $resource->getStatus());
            }
        });

        static::assertCount(2, $domain->getRepository()->findAll());

        $resources = $domain->updates($this->wrap($objects, $wrapped), true);
        static::assertInstanceOf(ResourceListInterface::class, $resources);

        static::assertTrue($resources->hasErrors());
        static::assertMatchesRegularExpression('/This value should not be blank./', $resources->get(0)->getErrors()->get(0)->getMessage());
        static::assertMatchesRegularExpression($this->getIntegrityViolationMessage(), $resources->get(1)->getErrors()->get(0)->getMessage());

        static::assertTrue($preEvent);
        static::assertTrue($postEvent);

        static::assertCount(2, $domain->getRepository()->findAll());
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpsertsAutoCommitWithErrorDatabase(bool $wrapped): void
    {
        $domain = $this->createDomain();

        $objects = $this->insertResources($domain, 2);

        $objects[0]->setDetail(null);
        $objects[0]->setDescription('test 1');
        $objects[1]->setDescription('test 2');

        $preEvent = false;
        $postEvent = false;

        $this->dispatcher->addListener(PreUpdatesEvent::class, function (PreUpdatesEvent $e) use (&$preEvent, $domain): void {
            $preEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::PENDING, $resource->getStatus());
            }
        });
        $this->dispatcher->addListener(PostUpdatesEvent::class, function (PostUpdatesEvent $e) use (&$postEvent, $domain): void {
            $postEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::ERROR, $resource->getStatus());
            }
        });

        static::assertCount(2, $domain->getRepository()->findAll());

        $resources = $domain->updates($this->wrap($objects, $wrapped), true);
        static::assertInstanceOf(ResourceListInterface::class, $resources);

        static::assertTrue($resources->hasErrors());

        static::assertCount(1, $resources->get(0)->getErrors());
        static::assertCount(1, $resources->get(1)->getErrors());

        static::assertMatchesRegularExpression($this->getIntegrityViolationMessage(), $resources->get(0)->getErrors()->get(0)->getMessage());
        static::assertMatchesRegularExpression('/Caused by previous internal database error/', $resources->get(1)->getErrors()->get(0)->getMessage());

        static::assertTrue($preEvent);
        static::assertTrue($postEvent);

        static::assertCount(2, $domain->getRepository()->findAll());
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdatesAutoCommitWithErrorValidationAndSuccess(bool $wrapped): void
    {
        $domain = $this->createDomain();
        $objects = $this->insertResources($domain, 2);

        $objects[0]->setName(null);
        $objects[1]->setDetail('New Detail 2');

        static::assertCount(2, $domain->getRepository()->findAll());
        $resources = $domain->updates($this->wrap($objects, $wrapped), true);
        static::assertCount(2, $domain->getRepository()->findAll());

        static::assertCount(2, $resources);
        static::assertInstanceOf(ResourceInterface::class, $resources->get(0));
        static::assertInstanceOf(ResourceInterface::class, $resources->get(1));

        static::assertSame(ResourceListStatutes::MIXED, $resources->getStatus());
        static::assertSame(ResourceStatutes::ERROR, $resources->get(0)->getStatus());
        static::assertSame(ResourceStatutes::UPDATED, $resources->get(1)->getStatus());
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testUpdatesAutoCommit(bool $wrapped): void
    {
        $this->runTestUpdates(true, $wrapped);
    }

    public function runTestUpdates($autoCommit, bool $wrapped): void
    {
        $domain = $this->createDomain();
        $objects = $this->insertResources($domain, 2);

        foreach ($objects as $i => $object) {
            $object->setName('New Bar '.($i + 1));
            $object->setDetail('New Detail '.($i + 1));
        }

        static::assertCount(2, $domain->getRepository()->findAll());
        $resources = $domain->updates($this->wrap($objects, $wrapped), $autoCommit);
        static::assertCount(2, $domain->getRepository()->findAll());

        static::assertCount(2, $resources);
        static::assertInstanceOf(ResourceInterface::class, $resources->get(0));
        static::assertInstanceOf(ResourceInterface::class, $resources->get(1));

        static::assertSame(ResourceListStatutes::SUCCESSFULLY, $resources->getStatus());
        static::assertSame(ResourceStatutes::UPDATED, $resources->get(0)->getStatus());
        static::assertSame(ResourceStatutes::UPDATED, $resources->get(1)->getStatus());
    }

    /**
     * @dataProvider getWrappedData
     */
    public function testInvalidObjectType(bool $wrapped): void
    {
        $this->expectException(\Klipper\Component\Resource\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Klipper\\Component\\Resource\\Tests\\Fixtures\\Entity\\Foo", "integer" given at the position "0"');

        $domain = $this->createDomain();
        /** @var object $object */
        $object = 42;

        $domain->update($this->wrap($object, $wrapped));
    }

    /**
     * @dataProvider getWrappedData
     *
     * @throws
     */
    public function testErrorIdentifier(bool $wrapped): void
    {
        $domain = $this->createDomain();
        /** @var Foo $object */
        $object = $domain->newInstance();
        $object->setName('Bar');
        $object->setDetail('Detail');

        $this->loadFixtures([]);

        $resource = $domain->update($this->wrap($object, $wrapped));
        static::assertFalse($resource->isValid());
        static::assertSame(ResourceStatutes::ERROR, $resource->getStatus());
        static::assertMatchesRegularExpression('/The resource cannot be updated because it has not an identifier/', $resource->getErrors()->get(0)->getMessage());
    }

    protected function runTestUpdateException(DomainInterface $domain, $object, $errorMessage): void
    {
        $preEvent = false;
        $postEvent = false;

        $this->dispatcher->addListener(PreUpdatesEvent::class, function (PreUpdatesEvent $e) use (&$preEvent, $domain): void {
            $preEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::PENDING, $resource->getStatus());
            }
        });
        $this->dispatcher->addListener(PostUpdatesEvent::class, function (PostUpdatesEvent $e) use (&$postEvent, $domain): void {
            $postEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::ERROR, $resource->getStatus());
            }
        });

        static::assertCount(1, $domain->getRepository()->findAll());

        $resource = $domain->update($object);
        static::assertCount(1, $resource->getErrors());
        static::assertMatchesRegularExpression($errorMessage, $resource->getErrors()->get(0)->getMessage());

        static::assertTrue($preEvent);
        static::assertTrue($postEvent);

        static::assertCount(1, $domain->getRepository()->findAll());
    }

    protected function runTestUpdatesException(DomainInterface $domain, array $objects, $errorMessage, $autoCommit = false): void
    {
        $preEvent = false;
        $postEvent = false;

        $this->dispatcher->addListener(PreUpdatesEvent::class, function (PreUpdatesEvent $e) use (&$preEvent, $domain): void {
            $preEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            foreach ($e->getResources() as $resource) {
                $this->assertSame(ResourceStatutes::PENDING, $resource->getStatus());
            }
        });
        $this->dispatcher->addListener(PostUpdatesEvent::class, function (PostUpdatesEvent $e) use (&$postEvent, $autoCommit, $domain): void {
            $postEvent = true;
            $this->assertSame($domain->getClass(), $e->getClass());
            $resources = $e->getResources();
            $this->assertCount(2, $resources);
            $this->assertSame(ResourceStatutes::ERROR, $resources[0]->getStatus());
            $this->assertSame($autoCommit ? ResourceStatutes::CANCELED
                : ResourceStatutes::ERROR, $resources[1]->getStatus());
        });

        static::assertCount(2, $domain->getRepository()->findAll());

        $resources = $domain->updates($objects);
        static::assertInstanceOf(ResourceListInterface::class, $resources);
        static::assertTrue($resources->hasErrors());

        /** @var ConstraintViolationListInterface $errors */
        $errors = $autoCommit
            ? $resources->get(0)->getErrors()
            : $resources->getErrors();
        static::assertCount(1, $errors);
        static::assertMatchesRegularExpression($errorMessage, $errors[0]->getMessage());

        static::assertTrue($preEvent);
        static::assertTrue($postEvent);

        static::assertCount(2, $domain->getRepository()->findAll());
        static::assertSame($autoCommit ? ResourceListStatutes::MIXED
            : ResourceListStatutes::ERROR, $resources->getStatus());
    }
}
