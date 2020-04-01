<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Handler;

/**
 * A form config interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FormConfigListInterface extends FormConfigInterface
{
    /**
     * Set the limit of the size list.
     *
     * @param null|int $limit The limit
     */
    public function setLimit(?int $limit): FormConfigListInterface;

    /**
     * Get the limit of the size list.
     */
    public function getLimit(): ?int;

    /**
     * Set the transactional mode.
     *
     * @param bool $transactional Check if the domain use the transactional mode
     */
    public function setTransactional(bool $transactional): FormConfigListInterface;

    /**
     * Check if the domain use the transactional mode.
     */
    public function isTransactional(): bool;

    /**
     * Find the record list in form data.
     *
     * @param array $data The form data
     */
    public function findList(array $data): array;

    /**
     * Convert the list of objects, and clean the list.
     *
     * @param array[] $list The list of record data
     *
     * @return object[]
     */
    public function convertObjects(array &$list): array;
}
