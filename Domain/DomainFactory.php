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

use Doctrine\Persistence\ManagerRegistry;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\Resource\Object\ObjectFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Resource domain factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DomainFactory implements DomainFactoryInterface
{
    protected ManagerRegistry $or;

    protected EventDispatcherInterface $ed;

    protected ObjectFactoryInterface $of;

    protected ValidatorInterface $validator;

    protected TranslatorInterface $translator;

    protected array $undeleteDisableFilters;

    protected bool $debug;

    protected array $resolveTargets = [];

    /**
     * @param ManagerRegistry          $or                     The doctrine registry
     * @param EventDispatcherInterface $ed                     The event dispatcher
     * @param ObjectFactoryInterface   $of                     The default value object factory
     * @param ValidatorInterface       $validator              The validator
     * @param TranslatorInterface      $translator             The translator
     * @param array                    $undeleteDisableFilters The undelete disable filters
     * @param bool                     $debug                  The debug mode
     */
    public function __construct(
        ManagerRegistry $or,
        EventDispatcherInterface $ed,
        ObjectFactoryInterface $of,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        array $undeleteDisableFilters = [],
        bool $debug = false
    ) {
        $this->or = $or;
        $this->ed = $ed;
        $this->of = $of;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->undeleteDisableFilters = $undeleteDisableFilters;
        $this->debug = $debug;
    }

    public function addResolveTargets(array $resolveTargets): DomainFactoryInterface
    {
        $this->resolveTargets = array_merge($this->resolveTargets, $resolveTargets);

        return $this;
    }

    public function isManagedClass(string $class): bool
    {
        return null !== ManagerUtils::getManager($this->or, $this->findClassName($class));
    }

    public function getManagedClass(string $class): string
    {
        return ManagerUtils::getRequiredManager($this->or, $this->findClassName($class))
            ->getClassMetadata($class)->getName();
    }

    public function create(string $class): DomainInterface
    {
        return new Domain(
            $class,
            ManagerUtils::getRequiredManager($this->or, $this->findClassName($class)),
            $this->of,
            $this->ed,
            $this->validator,
            $this->translator,
            $this->undeleteDisableFilters,
            $this->debug
        );
    }

    public function clear(): void
    {
        foreach ($this->or->getManagers() as $manager) {
            $manager->clear();
        }
    }

    /**
     * Find the class name by the the class name or the Doctrine resolved target.
     *
     * @param string $class The class name
     */
    protected function findClassName(string $class): string
    {
        return $this->resolveTargets[$class] ?? $class;
    }
}
