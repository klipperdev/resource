<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Resource list interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ResourceListInterface extends \Traversable, \Countable, \ArrayAccess
{
    /**
     * Get the status of action by the resource domain.
     */
    public function getStatus(): string;

    /**
     * Get the resource instance.
     *
     * @return ResourceInterface[]
     */
    public function getResources(): array;

    /**
     * Add a resource.
     *
     * @param ResourceInterface $resource The resource
     */
    public function add(ResourceInterface $resource): void;

    /**
     * Add resources.
     *
     * @param ResourceListInterface $otherList The other resources
     */
    public function addAll(ResourceListInterface $otherList);

    /**
     * Get all resources.
     *
     * @return ResourceInterface[]
     */
    public function all(): array;

    /**
     * Get a resource.
     *
     * @param int $offset The offset
     *
     * @throws \OutOfBoundsException When the offset does not exist
     */
    public function get($offset): ResourceInterface;

    /**
     * Check if the resource exist.
     *
     * @param int $offset The offset
     */
    public function has(int $offset): bool;

    /**
     * Set a resource.
     *
     * @param int               $offset   The offset
     * @param ResourceInterface $resource The resource
     */
    public function set(int $offset, ResourceInterface $resource): void;

    /**
     * Remove a resource.
     *
     * @param int $offset The offset
     */
    public function remove(int $offset): void;

    /**
     * Get the errors defined for this list (not include the children error).
     */
    public function getErrors(): ConstraintViolationListInterface;

    /**
     * Check if there is an error on resource list or children.
     */
    public function hasErrors(): bool;
}
