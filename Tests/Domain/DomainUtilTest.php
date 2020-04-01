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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Exception\DriverException;
use Klipper\Component\Resource\Domain\Domain;
use Klipper\Component\Resource\Domain\DomainUtil;
use Klipper\Component\Resource\Event\PostCreatesEvent;
use Klipper\Component\Resource\Event\PostDeletesEvent;
use Klipper\Component\Resource\Event\PostUndeletesEvent;
use Klipper\Component\Resource\Event\PostUpdatesEvent;
use Klipper\Component\Resource\Event\PostUpsertsEvent;
use Klipper\Component\Resource\Event\PreCreatesEvent;
use Klipper\Component\Resource\Event\PreDeletesEvent;
use Klipper\Component\Resource\Event\PreUndeletesEvent;
use Klipper\Component\Resource\Event\PreUpdatesEvent;
use Klipper\Component\Resource\Event\PreUpsertsEvent;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Resource\ResourceItem;
use Klipper\Component\Resource\ResourceList;
use Klipper\Component\Resource\ResourceListInterface;
use Klipper\Component\Resource\ResourceStatutes;
use Klipper\Component\Resource\Tests\Fixtures\Exception\MockDriverException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Tests case for Domain util.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DomainUtilTest extends TestCase
{
    public function testExtractDriverExceptionMessage(): void
    {
        /** @var DriverException|MockObject $ex */
        $ex = $this->getMockBuilder(DriverException::class)->disableOriginalConstructor()->getMock();

        $message = DomainUtil::getExceptionMessage($this->getTranslator(), $ex);

        static::assertSame('Database error', $message);
    }

    public function testExtractDriverExceptionMessageInDebug(): void
    {
        $rootMsg = 'SQLSTATE[HY000]: General error: 1364 Field \'foo\' doesn\'t have a default value';
        $rootEx = new MockDriverException($rootMsg);
        $prevEx = new MockDriverException('Previous exception', 1, $rootEx);
        $ex = new DriverException('Exception message', $prevEx);

        $message = DomainUtil::getExceptionMessage($this->getTranslator(), $ex, true);

        static::assertSame('Database error [Doctrine\DBAL\Exception\DriverException]: General error: 1364 Field \'foo\' doesn\'t have a default value', $message);
    }

    public function testTranslatableExceptionMessage(): void
    {
        $ex = new \Exception('domain.database_previous_error');
        $message = DomainUtil::getExceptionMessage($this->getTranslator(), $ex);

        static::assertSame('Caused by previous internal database error', $message);
    }

    public function testGetIdentifier(): void
    {
        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $meta->expects(static::once())
            ->method('getIdentifier')
            ->willReturn([
                'id',
            ])
        ;

        /** @var MockObject|ObjectManager $om */
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();
        $om->expects(static::once())
            ->method('getClassMetadata')
            ->with(\stdClass::class)
            ->willReturn($meta)
        ;

        $object = new \stdClass();
        $object->id = 42;

        $identifier = DomainUtil::getIdentifier($om, $object);

        static::assertSame($object->id, $identifier);
    }

    public function testGetIdentifierName(): void
    {
        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $meta->expects(static::once())
            ->method('getIdentifier')
            ->willReturn([
                'id',
            ])
        ;

        /** @var MockObject|ObjectManager $om */
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();
        $om->expects(static::once())
            ->method('getClassMetadata')
            ->with(\stdClass::class)
            ->willReturn($meta)
        ;

        $identifierName = DomainUtil::getIdentifierName($om, \stdClass::class);

        static::assertSame('id', $identifierName);
    }

    public function testGetEventClassCreate(): void
    {
        $classes = DomainUtil::getEventClasses(Domain::TYPE_CREATE);
        $validClasses = [PreCreatesEvent::class, PostCreatesEvent::class];
        static::assertSame($validClasses, $classes);
    }

    public function testGetEventClassUpdate(): void
    {
        $names = DomainUtil::getEventClasses(Domain::TYPE_UPDATE);
        $validNames = [PreUpdatesEvent::class, PostUpdatesEvent::class];
        static::assertSame($validNames, $names);
    }

    public function testGetEventClassUpsert(): void
    {
        $names = DomainUtil::getEventClasses(Domain::TYPE_UPSERT);
        $validNames = [PreUpsertsEvent::class, PostUpsertsEvent::class];
        static::assertSame($validNames, $names);
    }

    public function testGetEventClassDelete(): void
    {
        $names = DomainUtil::getEventClasses(Domain::TYPE_DELETE);
        $validNames = [PreDeletesEvent::class, PostDeletesEvent::class];
        static::assertSame($validNames, $names);
    }

    public function testGetEventClassUndelete(): void
    {
        $names = DomainUtil::getEventClasses(Domain::TYPE_UNDELETE);
        $validNames = [PreUndeletesEvent::class, PostUndeletesEvent::class];
        static::assertSame($validNames, $names);
    }

    public function testAddResourceError(): void
    {
        $errors = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();
        $errors->expects(static::once())
            ->method('add')
        ;

        /** @var MockObject|ResourceInterface $resource */
        $resource = $this->getMockBuilder(ResourceInterface::class)->getMock();
        $resource->expects(static::once())
            ->method('getErrors')
            ->willReturn($errors)
        ;

        DomainUtil::addResourceError($resource, 'Message error');
    }

    public function testExtractIdentifierInObjectList(): void
    {
        $identifiers = [
            new \stdClass(),
            5,
            new \stdClass(),
        ];
        $objects = [];
        $searchIds = DomainUtil::extractIdentifierInObjectList($identifiers, $objects);

        static::assertCount(2, $objects);
        static::assertSame($identifiers[0], $objects[0]);
        static::assertSame($identifiers[2], $objects[1]);

        static::assertCount(1, $searchIds);
        static::assertSame(5, $searchIds[0]);
    }

    public function testInjectErrorMessage(): void
    {
        $res = new ResourceItem(new \stdClass());

        static::assertSame(ResourceStatutes::PENDING, $res->getStatus());
        static::assertCount(0, $res->getErrors());

        $ex = new \Exception('Error message');
        DomainUtil::injectErrorMessage($this->getTranslator(), $res, $ex, true);

        static::assertSame(ResourceStatutes::ERROR, $res->getStatus());
        static::assertCount(1, $res->getErrors());
    }

    public function testInjectErrorMessageWithConstraintViolation(): void
    {
        $data = new \stdClass();
        $res = new ResourceItem($data);

        static::assertSame(ResourceStatutes::PENDING, $res->getStatus());
        static::assertCount(0, $res->getErrors());

        $list = new ConstraintViolationList();
        $list->add(new ConstraintViolation('Violation message', 'Violation message', [], $res->getRealData(), null, null));
        $list->add(new ConstraintViolation('Violation message 2', 'Violation message 2', [], $res->getRealData(), null, null));
        $ex = new ConstraintViolationException($list, 'Error message');
        DomainUtil::injectErrorMessage($this->getTranslator(), $res, $ex, true);

        static::assertSame(ResourceStatutes::ERROR, $res->getStatus());
        static::assertCount(2, $res->getErrors());
    }

    public function testOneAction(): void
    {
        $errors = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();
        $errors->expects(static::once())
            ->method('addAll')
        ;

        /** @var MockObject|ResourceInterface $resource */
        $resource = $this->getMockBuilder(ResourceInterface::class)->getMock();
        $resource->expects(static::once())
            ->method('getErrors')
            ->willReturn($errors)
        ;

        $listErrors = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();

        /** @var MockObject|ResourceListInterface $resourceList */
        $resourceList = $this->getMockBuilder(ResourceListInterface::class)->getMock();
        $resourceList->expects(static::once())
            ->method('getErrors')
            ->willReturn($listErrors)
        ;

        $resourceList->expects(static::atLeast(2))
            ->method('get')
            ->with(0)
            ->willReturn($resource)
        ;

        DomainUtil::oneAction($resourceList);
    }

    public function testMoveFlushErrorsInResource(): void
    {
        $resources = new ResourceList();
        $errors = new ConstraintViolationList();

        $resources->add(new ResourceItem(new \stdClass()));
        $resources->add(new ResourceItem(new \stdClass()));
        $resources->add(new ResourceItem(new \stdClass()));

        $errors->add(new ConstraintViolation('Violation message global', 'Violation message global', [], null, null, null));
        $errors->add(new ConstraintViolation('Violation message resource 1', 'Violation message resource 1', [], $resources->get(1)->getRealData(), null, null));

        static::assertCount(0, $resources->getErrors());
        static::assertCount(0, $resources->get(0)->getErrors());
        static::assertCount(0, $resources->get(1)->getErrors());
        static::assertCount(0, $resources->get(2)->getErrors());

        DomainUtil::moveFlushErrorsInResource($resources, $errors);

        static::assertCount(1, $resources->getErrors());
        static::assertCount(0, $resources->get(0)->getErrors());
        static::assertCount(1, $resources->get(1)->getErrors());
        static::assertCount(0, $resources->get(2)->getErrors());
    }

    public function testCancelAllSuccessResources(): void
    {
        $resList = new ResourceList();
        $resList->add(new ResourceItem(new \stdClass()));
        $resList->add(new ResourceItem(new \stdClass()));
        $resList->add(new ResourceItem(new \stdClass()));

        static::assertSame(ResourceStatutes::PENDING, $resList->getStatus());

        $resList->get(0)->setStatus(ResourceStatutes::ERROR);

        static::assertSame(ResourceStatutes::ERROR, $resList->get(0)->getStatus());
        static::assertSame(ResourceStatutes::PENDING, $resList->get(1)->getStatus());
        static::assertSame(ResourceStatutes::PENDING, $resList->get(2)->getStatus());

        DomainUtil::cancelAllSuccessResources($resList);

        static::assertSame(ResourceStatutes::ERROR, $resList->get(0)->getStatus());
        static::assertSame(ResourceStatutes::CANCELED, $resList->get(1)->getStatus());
        static::assertSame(ResourceStatutes::CANCELED, $resList->get(2)->getStatus());
    }

    /**
     * @throws
     */
    protected function getTranslator(): Translator
    {
        $translator = new Translator('en');
        $ref = new \ReflectionClass(ResourceInterface::class);
        $translator->addResource('xml', realpath(\dirname($ref->getFileName()).'/Resources/translations/KlipperResource.en.xlf'), 'en', 'KlipperResource');
        $translator->addResource('xml', realpath(\dirname($ref->getFileName()).'/Resources/translations/KlipperResource.en.xlf'), 'en', 'validators');
        $translator->addLoader('xml', new XliffFileLoader());

        return $translator;
    }
}
