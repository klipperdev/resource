<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Fixtures\Entity;

/**
 * Foo entity.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Foo
{
    protected ?int $id = null;

    protected ?string $name = null;

    protected ?string $description = null;

    protected ?string $detail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDetail(?string $detail): void
    {
        $this->detail = $detail;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }
}
