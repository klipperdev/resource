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

use Klipper\Component\Resource\Exception\InvalidArgumentException;

/**
 * Domain manager interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface DomainManagerInterface
{
    /**
     * Check if the class is managed.
     *
     * @param string $class The class name or the Doctrine resolved target
     */
    public function has(string $class): bool;

    /**
     * Get a resource domain.
     *
     * @param string $class The class name
     *
     * @throws InvalidArgumentException When the class of resource domain is not managed
     */
    public function get(string $class): DomainInterface;

    /**
     * Clear all managers.
     */
    public function clear(): void;
}
