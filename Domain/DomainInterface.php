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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Resource\ResourceListInterface;
use Symfony\Component\Form\FormInterface;

/**
 * A resource domain interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface DomainInterface
{
    /**
     * Get the doctrine object registry.
     */
    public function getObjectManager(): ObjectManager;

    /**
     * Get the class name for this resource domain.
     */
    public function getClass(): string;

    /**
     * Get the doctrine repository for this resource domain.
     *
     * @return EntityRepository|ObjectRepository
     */
    public function getRepository(): ObjectRepository;

    /**
     * Create the query builder for this domain.
     *
     * @param string      $alias   The alias of class in query
     * @param null|string $indexBy The index for the from
     */
    public function createQueryBuilder($alias = 'o', ?string $indexBy = null): QueryBuilder;

    /**
     * Generate a new resource instance with default values.
     *
     * @param array $options The options of klipper default value factory
     *
     * @return object
     */
    public function newInstance(array $options = []);

    /**
     * Create a resource.
     *
     * @param FormInterface|object|WrapperInterface $resource The object resource instance of defined class name
     */
    public function create($resource): ResourceInterface;

    /**
     * Create resources.
     *
     * Warning: It's recommended to limit the number of resources.
     *
     * @param FormInterface[]|object[]|WrapperInterface[] $resources  The list of object resource instance
     * @param bool                                        $autoCommit Commit transaction for each resource or all
     *                                                                (continue the action even if there is an error on a resource)
     */
    public function creates(array $resources, bool $autoCommit = false): ResourceListInterface;

    /**
     * Update a resource.
     *
     * @param FormInterface|object|WrapperInterface $resource The object resource
     */
    public function update($resource): ResourceInterface;

    /**
     * Update resources.
     *
     * Warning: It's recommended to limit the number of resources.
     *
     * @param FormInterface[]|object[]|WrapperInterface[] $resources  The list of object resource instance
     * @param bool                                        $autoCommit Commit transaction for each resource or all
     *                                                                (continue the action even if there is an error on a resource)
     */
    public function updates(array $resources, bool $autoCommit = false): ResourceListInterface;

    /**
     * Update or insert a resource.
     *
     * @param FormInterface|object|WrapperInterface $resource The object resource
     */
    public function upsert($resource): ResourceInterface;

    /**
     * Update or insert resources.
     *
     * Warning: It's recommended to limit the number of resources.
     *
     * @param FormInterface[]|object[]|WrapperInterface[] $resources  The list of object resource instance
     * @param bool                                        $autoCommit Commit transaction for each resource or all
     *                                                                (continue the action even if there is an error on a resource)
     */
    public function upserts(array $resources, bool $autoCommit = false): ResourceListInterface;

    /**
     * Delete a resource.
     *
     * @param object $resource The object resource
     * @param bool   $soft     Check if the delete must be hard or soft for the objects compatibles
     */
    public function delete($resource, bool $soft = true): ResourceInterface;

    /**
     * Delete resources.
     *
     * Warning: It's recommended to limit the number of resources.
     *
     * @param object[] $resources  The list of object resource instance
     * @param bool     $soft       Check if the delete must be hard or soft for the objects compatibles
     * @param bool     $autoCommit Commit transaction for each resource or all
     *                             (continue the action even if there is an error on a resource)
     */
    public function deletes(array $resources, bool $soft = true, bool $autoCommit = false): ResourceListInterface;

    /**
     * Undelete a resource.
     *
     * @param int|object|string $identifier The object or object identifier
     */
    public function undelete($identifier): ResourceInterface;

    /**
     * Undelete resources.
     *
     * Warning: It's recommended to limit the number of resources.
     *
     * @param int[]|object[]|string[] $identifiers The list of objects or object identifiers
     * @param bool                    $autoCommit  Commit transaction for each resource or all
     *                                             (continue the action even if there is an error on a resource)
     */
    public function undeletes(array $identifiers, bool $autoCommit = false): ResourceListInterface;
}
