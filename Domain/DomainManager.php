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

/**
 * Domain manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DomainManager implements DomainManagerInterface
{
    /**
     * @var DomainInterface[]
     */
    protected array $domains = [];

    protected DomainFactoryInterface $factory;

    /**
     * @param DomainFactoryInterface $factory The domain factory
     */
    public function __construct(DomainFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function has(string $class): bool
    {
        return isset($this->domains[$class])
            || $this->factory->isManagedClass($class);
    }

    public function get(string $class): DomainInterface
    {
        $class = $this->factory->getManagedClass($class);

        if (!isset($this->domains[$class])) {
            $this->domains[$class] = $this->factory->create($class);
        }

        return $this->domains[$class];
    }
}
