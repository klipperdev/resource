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

use Klipper\Component\Resource\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Resource interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ResourceInterface
{
    /**
     * Set status.
     *
     * @param string $status The status defined in ResourceStatutes class
     */
    public function setStatus(string $status): void;

    /**
     * Get the status of action by the resource domain.
     */
    public function getStatus(): string;

    /**
     * Get the data instance of this resource.
     *
     * @return FormInterface|object
     */
    public function getData();

    /**
     * Get the real resource data.
     *
     * @return object
     */
    public function getRealData();

    /**
     * Get the list of errors.
     */
    public function getErrors(): ConstraintViolationListInterface;

    /**
     * Get the form errors.
     *
     * @throws InvalidArgumentException When the data is not a form
     */
    public function getFormErrors(): FormErrorIterator;

    /**
     * Check if the resource is a resource for a form.
     */
    public function isForm(): bool;

    /**
     * Check if the resource has errors.
     */
    public function isValid(): bool;
}
