<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Domain;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\Resource\Event\ResourceEvent;
use Klipper\Component\Resource\Exception\InvalidConfigurationException;
use Klipper\Component\Resource\Object\ObjectFactoryInterface;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Resource\ResourceList;
use Klipper\Component\Resource\ResourceListInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A abstract class for resource domain.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractDomain implements DomainInterface
{
    public const TYPE_CREATE = 0;

    public const TYPE_UPDATE = 1;

    public const TYPE_UPSERT = 2;

    public const TYPE_DELETE = 3;

    public const TYPE_UNDELETE = 4;

    protected string $class;

    protected ?Connection $connection;

    protected ObjectManager $om;

    protected ObjectFactoryInterface $of;

    protected EventDispatcherInterface $ed;

    protected ValidatorInterface $validator;

    protected TranslatorInterface $translator;

    protected bool $debug;

    protected array $disableFilters = [];

    /**
     * @param string                   $class          The class name
     * @param ObjectManager            $om             The object manager
     * @param ObjectFactoryInterface   $of             The object factory
     * @param EventDispatcherInterface $ed             The event dispatcher
     * @param ValidatorInterface       $validator      The validator
     * @param TranslatorInterface      $translator     The translator
     * @param array                    $disableFilters The list of doctrine filters must be disabled for undelete resources
     * @param bool                     $debug          The debug mode
     */
    public function __construct(
        $class,
        ObjectManager $om,
        ObjectFactoryInterface $of,
        EventDispatcherInterface $ed,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        array $disableFilters = [],
        bool $debug = false
    ) {
        $this->om = $om;
        $this->of = $of;
        $this->ed = $ed;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->debug = $debug;

        try {
            $this->class = $om->getClassMetadata($class)->getName();
        } catch (MappingException $e) {
            $msg = sprintf('The "%s" class is not managed by doctrine object manager', $class);

            throw new InvalidConfigurationException($msg, 0, $e);
        }

        if ($om instanceof EntityManagerInterface) {
            $this->disableFilters = $disableFilters;
            $this->connection = $om->getConnection();
        }
    }

    public function getObjectManager(): ObjectManager
    {
        return $this->om;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getRepository(): ObjectRepository
    {
        return $this->om->getRepository($this->getClass());
    }

    public function newInstance(array $options = [])
    {
        return $this->of->create($this->getClass(), $options);
    }

    public function create($resource): ResourceInterface
    {
        return DomainUtil::oneAction($this->creates([$resource], true));
    }

    public function creates(array $resources, bool $autoCommit = false): ResourceListInterface
    {
        return $this->persist($resources, $autoCommit, Domain::TYPE_CREATE);
    }

    public function update($resource): ResourceInterface
    {
        return DomainUtil::oneAction($this->updates([$resource], true));
    }

    public function updates(array $resources, bool $autoCommit = false): ResourceListInterface
    {
        return $this->persist($resources, $autoCommit, Domain::TYPE_UPDATE);
    }

    public function upsert($resource): ResourceInterface
    {
        return DomainUtil::oneAction($this->upserts([$resource], true));
    }

    public function upserts(array $resources, bool $autoCommit = false): ResourceListInterface
    {
        return $this->persist($resources, $autoCommit, Domain::TYPE_UPSERT);
    }

    public function delete($resource, bool $soft = true): ResourceInterface
    {
        return DomainUtil::oneAction($this->deletes([$resource], $soft, true));
    }

    public function undelete($identifier): ResourceInterface
    {
        return DomainUtil::oneAction($this->undeletes([$identifier], true));
    }

    /**
     * Dispatch the event.
     */
    protected function dispatchEvent(ResourceEvent $event): ResourceEvent
    {
        $this->ed->dispatch($event);

        return $event;
    }

    /**
     * Disable the doctrine filters.
     *
     * @return array The previous values of filters
     */
    protected function disableFilters(): array
    {
        $previous = SqlFilterUtil::findFilters($this->om, $this->disableFilters);
        SqlFilterUtil::disableFilters($this->om, $previous);

        return $previous;
    }

    /**
     * Enable the doctrine filters.
     *
     * @param array $previousValues the previous values of filters
     */
    protected function enableFilters(array $previousValues = []): void
    {
        SqlFilterUtil::enableFilters($this->om, $previousValues);
    }

    /**
     * Persist the resources.
     *
     * Warning: It's recommended to limit the number of resources.
     *
     * @param FormInterface[]|object[] $resources      The list of object resource instance
     * @param bool                     $autoCommit     Commit transaction for each resource or all
     *                                                 (continue the action even if there is an error on a resource)
     * @param int                      $type           The type of persist action
     * @param ResourceInterface[]      $errorResources The error resources
     */
    abstract protected function persist(array $resources, bool $autoCommit, int $type, array $errorResources = []): ResourceList;
}
