<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Event;

use Klipper\Component\Resource\ResourceListInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The resource event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class ResourceEvent extends Event
{
    private string $class;

    private ResourceListInterface $resources;

    /**
     * @param string                $class     The class name of resources
     * @param ResourceListInterface $resources The list of resource instances
     */
    public function __construct(string $class, ResourceListInterface $resources)
    {
        $this->class = $class;
        $this->resources = $resources;
    }

    /**
     * Get the class name of resources.
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get the list of resource instances.
     */
    public function getResources(): ResourceListInterface
    {
        return $this->resources;
    }

    /**
     * Check if the the event resource is the specified class.
     *
     * @param string $class The class name
     */
    public function is(string $class): bool
    {
        return is_a($this->class, $class, true) || \in_array($class, class_implements($class), true);
    }
}
