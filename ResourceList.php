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
 * Resource list.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ResourceList extends AbstractResourceList
{
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->resources);
    }

    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        if ($this->getErrors()->count() > 0) {
            return true;
        }

        foreach ($this->resources as $i => $resource) {
            if (!$resource->isValid()
                    && \in_array($resource->getStatus(), [ResourceStatutes::ERROR, ResourceStatutes::PENDING], true)) {
                return true;
            }
        }

        return false;
    }

    protected function refreshStatus(): void
    {
        $countPending = 0;
        $countCancel = 0;
        $countError = 0;
        $countSuccess = 0;

        foreach ($this->resources as $resource) {
            switch ($resource->getStatus()) {
                case ResourceStatutes::PENDING:
                    $countPending++;

                    break;
                case ResourceStatutes::CANCELED:
                    $countCancel++;

                    break;
                case ResourceStatutes::ERROR:
                    $countError++;

                    break;
                default:
                    $countSuccess++;

                    break;
            }
        }

        $this->status = $this->getStatusValue($countPending, $countCancel, $countError, $countSuccess);
    }

    /**
     * Get the final status value.
     */
    private function getStatusValue(int $countPending, int $countCancel, int $countError, int $countSuccess): string
    {
        $status = ResourceListStatutes::SUCCESSFULLY;
        $count = $this->count();

        if ($count > 0) {
            $status = ResourceListStatutes::MIXED;

            if ($count === $countPending) {
                $status = ResourceListStatutes::PENDING;
            } elseif ($count === $countCancel) {
                $status = ResourceListStatutes::CANCEL;
            } elseif ($count === $countError) {
                $status = ResourceListStatutes::ERROR;
            } elseif ($count === $countSuccess) {
                $status = ResourceListStatutes::SUCCESSFULLY;
            }
        }

        return $status;
    }
}
