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

use Klipper\Component\Resource\Exception\ClassNotInstantiableException;

/**
 * The action statutes for the list of resource domains.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class ResourceListStatutes
{
    /**
     * The ResourceStatutes::PENDING status is used when none of the resources is
     * executed.
     *
     * This status is used in Klipper\Component\Resource\Event\ResourceEvent
     * and Klipper\Component\Resource\Domain\DomainInterface instances.
     */
    public const PENDING = 'pending';

    /**
     * The ResourceStatutes::SUCCESSFULLY status is used when the all resources in the
     * list has been executed successfully.
     *
     * This status is used in Klipper\Component\Resource\Event\ResourceEvent
     * and Klipper\Component\Resource\Domain\DomainInterface instances.
     */
    public const SUCCESSFULLY = 'successfully';

    /**
     * The ResourceStatutes::MIXED status is used when several different statuses are
     * in the list.
     *
     * This status is used in Klipper\Component\Resource\Event\ResourceEvent
     * and Klipper\Component\Resource\Domain\DomainInterface instances.
     */
    public const MIXED = 'mixed';

    /**
     * The ResourceStatutes::CANCEL status is used when the all resources in the
     * list has canceled.
     *
     * This status is used in Klipper\Component\Resource\Event\ResourceEvent
     * and Klipper\Component\Resource\Domain\DomainInterface instances.
     */
    public const CANCEL = 'cancel';

    /**
     * The ResourceStatutes::ERROR status is used when the all resources in the
     * list has errors.
     *
     * This status is used in Klipper\Component\Resource\Event\ResourceEvent
     * and Klipper\Component\Resource\Domain\DomainInterface instances.
     */
    public const ERROR = 'error';

    /**
     * Constructor.
     *
     * @throws ClassNotInstantiableException
     */
    public function __construct()
    {
        throw new ClassNotInstantiableException(__CLASS__);
    }
}
