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

use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Resource\ResourceListInterface;
use Klipper\Component\Resource\ResourceStatutes;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * A base class for resource domain.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class BaseDomain extends AbstractDomain
{
    /**
     * Do the flush transaction for auto commit.
     *
     * @param ResourceInterface $resource   The resource
     * @param bool              $autoCommit The auto commit
     * @param bool              $skipped    Check if the resource is skipped
     *
     * @return bool Returns if there is an flush error
     */
    protected function doAutoCommitFlushTransaction(ResourceInterface $resource, bool $autoCommit, bool $skipped = false): bool
    {
        $hasFlushError = $resource->getErrors()->count() > 0;

        if ($autoCommit && !$skipped && !$hasFlushError) {
            $rErrors = $this->flushTransaction($resource->getRealData());
            $resource->getErrors()->addAll($rErrors);
            $hasFlushError = $rErrors->count() > 0;
        }

        return $hasFlushError;
    }

    /**
     * Do flush the final transaction for non auto commit.
     *
     * @param ResourceListInterface $resources  The list of object resource instance
     * @param bool                  $autoCommit Commit transaction for each resource or all
     *                                          (continue the action even if there is an error on a resource)
     * @param bool                  $hasError   Check if there is an error
     */
    protected function doFlushFinalTransaction(ResourceListInterface $resources, bool $autoCommit, bool $hasError): void
    {
        if (!$autoCommit) {
            if ($hasError) {
                $this->cancelTransaction();
                DomainUtil::cancelAllSuccessResources($resources);
            } else {
                $errors = $this->flushTransaction();
                DomainUtil::moveFlushErrorsInResource($resources, $errors);
            }
        }
    }

    /**
     * Finalize the action for a resource.
     *
     * @return bool Returns the new hasError value
     */
    protected function finalizeResourceStatus(ResourceInterface $resource, string $status, bool $hasError): bool
    {
        if ($resource->isValid()) {
            $resource->setStatus($status);
        } else {
            $hasError = true;
            $resource->setStatus(ResourceStatutes::ERROR);
            $this->om->detach($resource->getRealData());
        }

        return $hasError;
    }

    /**
     * Begin automatically the database transaction.
     *
     * @param bool $autoCommit Check if each resource must be flushed immediately or in the end
     */
    protected function beginTransaction(bool $autoCommit = false): void
    {
        if (!$autoCommit && null !== $this->connection) {
            $this->connection->beginTransaction();
        }
    }

    /**
     * Flush data in database with automatic declaration of the transaction for the collection.
     *
     * @param null|object $object The resource for auto commit or null for flush at the end
     */
    protected function flushTransaction($object = null): ConstraintViolationList
    {
        $violations = new ConstraintViolationList();

        try {
            $this->om->flush();

            if (null !== $this->connection && null === $object) {
                $this->connection->commit();
            }
        } catch (\Throwable $e) {
            $this->flushTransactionException($e, $violations, $object);
        }

        return $violations;
    }

    /**
     * Do the action when there is an exception on flush transaction.
     *
     * @param \Throwable                       $e          The exception on flush transaction
     * @param ConstraintViolationListInterface $violations The constraint violation list
     * @param null|object                      $object     The resource for auto commit or null for flush at the end
     *
     * @throws
     */
    protected function flushTransactionException(\Throwable $e, ConstraintViolationListInterface $violations, $object = null): void
    {
        if (null !== $this->connection && null === $object) {
            $this->connection->rollback();
        }

        if ($e instanceof ConstraintViolationException) {
            $violations->addAll($e->getConstraintViolations());
        } else {
            $message = DomainUtil::getThrowableMessage($this->translator, $e, $this->debug);

            $violations->add(new ConstraintViolation($message, $message, [], $object, null, null, null, null, null, $e));
        }
    }

    /**
     * Cancel transaction.
     *
     * @throws
     */
    protected function cancelTransaction(): void
    {
        if (null !== $this->connection) {
            $this->connection->rollBack();
        }
    }

    /**
     * Validate the resource and get the error list.
     *
     * @param ResourceInterface $resource The resource
     * @param int               $type     The type of persist
     */
    protected function validateResource(ResourceInterface $resource, int $type): void
    {
        if (!$resource->isValid()) {
            return;
        }

        $idError = $this->getErrorIdentifier($resource->getRealData(), $type);
        $data = $resource->getData();

        if ($data instanceof FormInterface) {
            if (!$data->isSubmitted()) {
                $data->submit([]);
            }
        } else {
            $groupValidation = $data instanceof ValidationWrapperInterface ? $data->getValidationGroups() : null;
            $data = $data instanceof WrapperInterface ? $data->getData() : $data;
            $errors = $this->validator->validate($data, null, $groupValidation);
            $resource->getErrors()->addAll($errors);
        }

        if (null !== $idError) {
            $resource->getErrors()->add(new ConstraintViolation($idError, $idError, [], $resource->getRealData(), null, null));
        }
    }

    /**
     * Get the error of identifier.
     *
     * @param object $object The object data
     * @param int    $type   The type of persist
     */
    protected function getErrorIdentifier(object $object, int $type): ?string
    {
        $idValue = DomainUtil::getIdentifier($this->om, $object);
        $idError = null;

        if (Domain::TYPE_CREATE === $type && null !== $idValue) {
            $idError = $this->translator->trans('domain.identifier.error_create', [], 'KlipperResource');
        } elseif (Domain::TYPE_UPDATE === $type && null === $idValue) {
            $idError = $this->translator->trans('domain.identifier.error_update', [], 'KlipperResource');
        } elseif (Domain::TYPE_DELETE === $type && null === $idValue) {
            $idError = $this->translator->trans('domain.identifier.error_delete', [], 'KlipperResource');
        } elseif (Domain::TYPE_UNDELETE === $type && null === $idValue) {
            $idError = $this->translator->trans('domain.identifier.error_undeleted', [], 'KlipperResource');
        }

        return $idError;
    }

    /**
     * Get the success status.
     *
     * @param int    $type   The type of persist
     * @param object $object The resource instance
     */
    protected function getSuccessStatus(int $type, object $object): string
    {
        if (Domain::TYPE_CREATE === $type) {
            return ResourceStatutes::CREATED;
        }
        if (Domain::TYPE_UPDATE === $type) {
            return ResourceStatutes::UPDATED;
        }
        if (Domain::TYPE_UNDELETE === $type) {
            return ResourceStatutes::UNDELETED;
        }

        return null === DomainUtil::getIdentifier($this->om, $object)
            ? ResourceStatutes::CREATED
            : ResourceStatutes::UPDATED;
    }
}
