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

use Klipper\Component\Resource\Exception\OutOfBoundsException;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Abstract Resource list.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractResourceList implements \IteratorAggregate, ResourceListInterface
{
    protected ?string $status = null;

    /**
     * @var ResourceInterface[]
     */
    protected array $resources = [];

    protected ConstraintViolationListInterface $errors;

    /**
     * @var null|ConstraintViolationListInterface|FormErrorIterator[]
     */
    protected $childrenErrors;

    /**
     * @param ResourceInterface[]              $resources The list of resource
     * @param ConstraintViolationListInterface $errors    The list of errors
     */
    public function __construct(
        array $resources = [],
        ?ConstraintViolationListInterface $errors = null
    ) {
        $this->errors = $errors ?? new ConstraintViolationList();

        foreach ($resources as $resource) {
            $this->add($resource);
        }
    }

    public function getStatus(): string
    {
        if (null === $this->status) {
            $this->refreshStatus();
        }

        return $this->status;
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function add(ResourceInterface $resource): void
    {
        $this->reset();
        $this->resources[] = $resource;
    }

    public function addAll(ResourceListInterface $otherList): void
    {
        $this->reset();

        foreach ($otherList as $resource) {
            $this->resources[] = $resource;
        }
    }

    public function all(): array
    {
        return $this->resources;
    }

    /**
     * @param mixed $offset
     */
    public function get($offset): ResourceInterface
    {
        if (!isset($this->resources[$offset])) {
            throw new OutOfBoundsException(sprintf('The offset "%s" does not exist.', $offset));
        }

        return $this->resources[$offset];
    }

    public function has(int $offset): bool
    {
        return isset($this->resources[$offset]);
    }

    public function set(int $offset, ResourceInterface $resource): void
    {
        $this->reset();
        $this->resources[$offset] = $resource;
    }

    public function remove(int $offset): void
    {
        $this->reset();
        unset($this->resources[$offset]);
    }

    public function count(): int
    {
        return \count($this->resources);
    }

    /**
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     */
    public function offsetGet($offset): ResourceInterface
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $resource
     */
    public function offsetSet($offset, $resource): void
    {
        if (null === $offset) {
            $this->add($resource);
        } else {
            $this->set($offset, $resource);
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * Reset the value of status and children errors.
     */
    protected function reset(): void
    {
        $this->status = null;
        $this->childrenErrors = null;
    }

    /**
     * Refresh the status of this list.
     */
    abstract protected function refreshStatus(): void;
}
