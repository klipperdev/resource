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

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
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
    /**
     * @var string
     */
    protected $action;

    /**
     * @var bool
     */
    protected $useConstraint;

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
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->doException($entity);
        }
    }

    /**
     * @param object $entity The entity
     *
     * @throws \Exception When the entity does not deleted
     */
    public function doException($entity): void
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
