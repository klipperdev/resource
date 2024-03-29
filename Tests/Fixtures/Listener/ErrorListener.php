<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Fixtures\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Doctrine ORM error listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ErrorListener
{
    protected string $action;

    protected bool $useConstraint;

    public function __construct($action, $useConstraint = false)
    {
        $this->action = (string) $action;
        $this->useConstraint = $useConstraint;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->doException($args->getObject());
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $this->doException($args->getObject());
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->doException($entity);
        }
    }

    /**
     * @param object $entity The entity
     *
     * @throws \Throwable When the entity does not deleted
     */
    public function doException(object $entity): void
    {
        if ($this->useConstraint) {
            $message = 'The entity does not '.$this->action.' (violation exception)';
            $violation = new ConstraintViolation($message, $message, [], $entity, null, null);
            $list = new ConstraintViolationList([$violation]);

            throw new ConstraintViolationException($list);
        }

        throw new \Exception('The entity does not '.$this->action.' (exception)');
    }
}
