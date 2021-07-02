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

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\Persistence\ObjectManager;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Resource\Event\PostCreatesEvent;
use Klipper\Component\Resource\Event\PostDeletesEvent;
use Klipper\Component\Resource\Event\PostUndeletesEvent;
use Klipper\Component\Resource\Event\PostUpdatesEvent;
use Klipper\Component\Resource\Event\PostUpsertsEvent;
use Klipper\Component\Resource\Event\PreCreatesEvent;
use Klipper\Component\Resource\Event\PreDeletesEvent;
use Klipper\Component\Resource\Event\PreUndeletesEvent;
use Klipper\Component\Resource\Event\PreUpdatesEvent;
use Klipper\Component\Resource\Event\PreUpsertsEvent;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Klipper\Component\Resource\ResourceInterface;
use Klipper\Component\Resource\ResourceListInterface;
use Klipper\Component\Resource\ResourceStatutes;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Util for domain.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class DomainUtil
{
    /**
     * Get the value of resource identifier.
     *
     * @param ObjectManager $om     The doctrine object manager
     * @param object        $object The resource object
     *
     * @return null|int|string
     */
    public static function getIdentifier(ObjectManager $om, object $object)
    {
        $propertyAccess = PropertyAccess::createPropertyAccessor();
        $meta = $om->getClassMetadata(ClassUtils::getClass($object));
        $ids = $meta->getIdentifier();
        $value = null;

        foreach ($ids as $id) {
            $idVal = $propertyAccess->getValue($object, $id);

            if (null !== $idVal) {
                $value = $idVal;

                break;
            }
        }

        return $value;
    }

    /**
     * Get the name of identifier.
     *
     * @param ObjectManager $om        The doctrine object manager
     * @param string        $className The class name
     */
    public static function getIdentifierName(ObjectManager $om, string $className): string
    {
        $meta = $om->getClassMetadata($className);
        $ids = $meta->getIdentifier();

        return implode('', $ids);
    }

    /**
     * Get the event names of persist action.
     *
     * @param int $type The type of persist
     *
     * @return array The list of pre event name and post event name
     */
    public static function getEventClasses(int $type): array
    {
        $names = [PreUpsertsEvent::class, PostUpsertsEvent::class];

        if (Domain::TYPE_CREATE === $type) {
            $names = [PreCreatesEvent::class, PostCreatesEvent::class];
        } elseif (Domain::TYPE_UPDATE === $type) {
            $names = [PreUpdatesEvent::class, PostUpdatesEvent::class];
        } elseif (Domain::TYPE_DELETE === $type) {
            $names = [PreDeletesEvent::class, PostDeletesEvent::class];
        } elseif (Domain::TYPE_UNDELETE === $type) {
            $names = [PreUndeletesEvent::class, PostUndeletesEvent::class];
        }

        return $names;
    }

    /**
     * Extract the identifier that are not a object.
     *
     * @param array $identifiers The list containing identifier or object
     * @param array $objects     The real objects (by reference)
     *
     * @return array The identifiers that are not a object
     */
    public static function extractIdentifierInObjectList(array $identifiers, array &$objects): array
    {
        $searchIds = [];

        foreach ($identifiers as $identifier) {
            if (\is_object($identifier)) {
                $objects[] = $identifier;

                continue;
            }
            $searchIds[] = $identifier;
        }

        return $searchIds;
    }

    /**
     * Inject the list errors in the first resource, and return the this first resource.
     *
     * @param ResourceListInterface $resources The resource list
     *
     * @return ResourceInterface The first resource
     */
    public static function oneAction(ResourceListInterface $resources): ResourceInterface
    {
        $resources->get(0)->getErrors()->addAll($resources->getErrors());

        return $resources->get(0);
    }

    /**
     * Move the flush errors in each resource if the root object is present in constraint violation.
     *
     * @param ResourceListInterface            $resources The list of resources
     * @param ConstraintViolationListInterface $errors    The list of flush errors
     */
    public static function moveFlushErrorsInResource(ResourceListInterface $resources, ConstraintViolationListInterface $errors): void
    {
        if ($errors->count() > 0) {
            $maps = static::getMapErrors($errors);

            foreach ($resources->all() as $resource) {
                $resource->setStatus(ResourceStatutes::ERROR);
                $hash = spl_object_hash($resource->getRealData());
                if (isset($maps[$hash])) {
                    $resource->getErrors()->add($maps[$hash]);
                    unset($maps[$hash]);
                }
            }

            foreach ($maps as $error) {
                $resources->getErrors()->add($error);
            }
        }
    }

    /**
     * Cancel all resource in list that have an successfully status.
     *
     * @param ResourceListInterface $resources The list of resources
     */
    public static function cancelAllSuccessResources(ResourceListInterface $resources): void
    {
        foreach ($resources->all() as $resource) {
            if (ResourceStatutes::ERROR !== $resource->getStatus()) {
                $resource->setStatus(ResourceStatutes::CANCELED);
            }
        }
    }

    /**
     * Get the throwable message.
     *
     * @param TranslatorInterface $translator The translator
     * @param \Throwable          $exception  The exception
     * @param bool                $debug      The debug mode
     */
    public static function getThrowableMessage(TranslatorInterface $translator, \Throwable $exception, bool $debug = false): string
    {
        if ($exception instanceof DriverException) {
            $message = static::getDatabaseErrorMessage($translator, $exception, $debug);

            return static::extractDriverThrowableMessage($exception, $message, $debug);
        }

        if ($translator instanceof TranslatorBagInterface
                && $translator->getCatalogue()->has($exception->getMessage(), 'validators')) {
            return $translator->trans($exception->getMessage(), [], 'validators');
        }

        return $debug
            ? $exception->getMessage()
            : static::getDatabaseErrorMessage($translator, $exception, $debug);
    }

    /**
     * Add the error in resource.
     *
     * @param ResourceInterface $resource The resource
     * @param string            $message  The error message
     * @param null|\Throwable   $e        The exception
     */
    public static function addResourceError(ResourceInterface $resource, string $message, ?\Throwable $e = null): void
    {
        $resource->setStatus(ResourceStatutes::ERROR);
        $resource->getErrors()->add(new ConstraintViolation($message, $message, [], $resource->getRealData(), null, null, null, null, null, $e));
    }

    /**
     * Inject the exception message in resource error list.
     *
     * @param TranslatorInterface $translator The translator
     * @param ResourceInterface   $resource   The resource
     * @param \Throwable          $e          The exception on persist action
     * @param bool                $debug      The debug mode
     *
     * @throws \Error|\Throwable
     */
    public static function injectErrorMessage(TranslatorInterface $translator, ResourceInterface $resource, \Throwable $e, bool $debug = false): bool
    {
        if ($e instanceof \Error) {
            throw $e;
        }
        if ($e instanceof ConstraintViolationException) {
            $resource->setStatus(ResourceStatutes::ERROR);
            $resource->getErrors()->addAll($e->getConstraintViolations());
        } else {
            static::addResourceError($resource, static::getThrowableMessage($translator, $e, $debug), $e);
        }

        return true;
    }

    /**
     * Get the map of object hash and constraint violation list.
     *
     * @return array The map of object hash and constraint violation list
     */
    protected static function getMapErrors(ConstraintViolationListInterface $errors): array
    {
        $maps = [];
        $size = $errors->count();

        for ($i = 0; $i < $size; ++$i) {
            $root = $errors->get($i)->getRoot();

            if (\is_object($root)) {
                $maps[spl_object_hash($errors->get($i)->getRoot())] = $errors->get($i);
            } else {
                $maps[] = $errors->get($i);
            }
        }

        return $maps;
    }

    /**
     * Format pdo driver exception.
     *
     * @param DriverException $exception The exception
     * @param string          $message   The message
     * @param bool            $debug     The debug mode
     */
    protected static function extractDriverThrowableMessage(DriverException $exception, string $message, bool $debug = false): string
    {
        if ($debug && null !== $exception->getPrevious()) {
            $prevMessage = static::getFirstThrowable($exception)->getMessage();
            $pos = strpos($prevMessage, ':');

            if ($pos > 0 && 0 === strpos($prevMessage, 'SQLSTATE[')) {
                $message .= ': '.trim(substr($prevMessage, $pos + 1));
            }
        }

        return $message;
    }

    /**
     * Get the initial exception.
     */
    protected static function getFirstThrowable(\Throwable $exception): \Throwable
    {
        if (null !== $exception->getPrevious()) {
            return static::getFirstThrowable($exception->getPrevious());
        }

        return $exception;
    }

    /**
     * Get the translated message for the error database.
     *
     * @param TranslatorInterface $translator The translator
     * @param \Throwable          $exception  The exception
     * @param bool                $debug      The debug mode
     */
    protected static function getDatabaseErrorMessage(TranslatorInterface $translator, \Throwable $exception, bool $debug = false): string
    {
        $message = $translator->trans('domain.database_error', [], 'KlipperResource');

        if ($debug) {
            $message .= ' ['.\get_class($exception).']';
        }

        return $message;
    }
}
