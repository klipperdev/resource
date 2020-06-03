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

use Klipper\Component\Resource\Exception\InvalidResourceException;

/**
 * A form config list.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class FormConfigList extends FormConfig implements FormConfigListInterface
{
    protected ?int $limit = null;

    protected bool $transactional = true;

    public function setLimit(?int $limit): FormConfigListInterface
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setTransactional(bool $transactional): FormConfigListInterface
    {
        $this->transactional = $transactional;

        return $this;
    }

    public function isTransactional(): bool
    {
        return $this->transactional;
    }

    public function findList(array $data): array
    {
        if (!isset($data['records'])) {
            throw new InvalidResourceException('The "records" field is required');
        }

        if (\array_key_exists('transaction', $data)) {
            $this->setTransactional($data['transaction']);
        }

        return $data['records'];
    }
}
