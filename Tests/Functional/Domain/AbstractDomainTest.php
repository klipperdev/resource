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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Klipper\Component\DefaultValue\ObjectFactory;
use Klipper\Component\DefaultValue\ObjectRegistry;
use Klipper\Component\DefaultValue\ResolvedObjectTypeFactory;
use Klipper\Component\Resource\Domain\Domain;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Domain\Wrapper;
use Klipper\Component\Resource\Object\DefaultValueObjectFactory;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Resource\Tests\Fixtures\Entity\Bar;
use Klipper\Component\Resource\Tests\Fixtures\Entity\Foo;
use Klipper\Component\Resource\Tests\Fixtures\Listener\SoftDeletableSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Abstract class for Functional tests for Domain.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractDomainTest extends TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var DefaultValueObjectFactory
     */
    protected $objectFactory;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var SoftDeletableSubscriber
     */
    protected $softDeletable;

    protected function setUp(): void
    {
        $config = Setup::createXMLMetadataConfiguration([
            __DIR__.'/../../Fixtures/config/doctrine',
        ], true);
        $connectionOptions = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $this->em = EntityManager::create($connectionOptions, $config);

        $this->softDeletable = new SoftDeletableSubscriber();
        $this->em->getEventManager()->addEventSubscriber($this->softDeletable);

        $this->dispatcher = new EventDispatcher();

        $resolvedTypeFactory = new ResolvedObjectTypeFactory();
        $objectRegistry = new ObjectRegistry([], $resolvedTypeFactory);
        $dvof = new ObjectFactory($objectRegistry, $resolvedTypeFactory);
        $this->objectFactory = new DefaultValueObjectFactory($dvof);

        $this->validator = Validation::createValidatorBuilder()
            ->addXmlMapping(__DIR__.'/../../Fixtures/config/validation.xml')
            ->getValidator()
        ;

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($this->validator))
            ->getFormFactory()
        ;

        $this->translator = new Translator('en');
        $ref = new \ReflectionClass(ResourceInterface::class);
        $this->translator->addResource('xml', realpath(\dirname($ref->getFileName()).'/Resources/translations/KlipperResource.en.xlf'), 'en', 'KlipperResource');
        $this->translator->addLoader('xml', new XliffFileLoader());
    }

    protected function tearDown(): void
    {
        $tool = new SchemaTool($this->em);
        $tool->dropDatabase();
    }

    /**
     * Reset database and load the fixtures.
     *
     * @param array $fixtures The fixtures
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function loadFixtures(array $fixtures): void
    {
        $tool = new SchemaTool($this->em);
        $tool->dropDatabase();
        $this->em->getConnection()->getSchemaManager()->createDatabase($this->em->getConnection()->getDatabase());
        $tool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    /**
     * Create resource domain.
     *
     * @param string $class
     *
     * @return Domain
     */
    protected function createDomain($class = Foo::class)
    {
        return new Domain(
            $class,
            $this->em,
            $this->objectFactory,
            $this->dispatcher,
            $this->validator,
            $this->translator,
            ['soft_deletable'],
            true
        );
    }

    /**
     * Insert object in database.
     *
     * @return Foo
     */
    protected function insertResource(DomainInterface $domain)
    {
        return current($this->insertResources($domain, 1));
    }

    /**
     * Insert objects in database.
     *
     * @param int $size
     *
     * @return Bar[]|Foo[]
     */
    protected function insertResources(DomainInterface $domain, $size)
    {
        $this->loadFixtures([]);

        $objects = [];

        for ($i = 0; $i < $size; ++$i) {
            /** @var Bar|Foo $object */
            $object = $domain->newInstance();
            $object->setName('Bar '.($i + 1));
            $object->setDetail('Detail '.($i + 1));
            $this->em->persist($object);
            $objects[] = $object;
        }

        $this->em->flush();

        return $objects;
    }

    protected function getIntegrityViolationMessage()
    {
        if (\PHP_VERSION_ID >= 50500 && !\defined('HHVM_VERSION')) {
            return '/Integrity constraint violation: (\d+) NOT NULL constraint failed: foo.detail/';
        }

        return '/Integrity constraint violation: (\d+) foo.detail may not be NULL/';
    }

    /**
     * Wrap the data.
     *
     * @param object|object[] $data    The data
     * @param bool            $wrapped Check if the data must be wrapped
     *
     * @return object|object[]|Wrapper|Wrapper[]
     */
    protected function wrap($data, bool $wrapped)
    {
        if ($wrapped) {
            if (\is_array($data)) {
                foreach ($data as $i => $val) {
                    $data[$i] = new Wrapper($val);
                }
            } else {
                $data = new Wrapper($data);
            }
        }

        return $data;
    }
}
