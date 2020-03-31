<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests;

use Klipper\Component\Resource\ResourceStatutes;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ResourceStatutesTest extends TestCase
{
    public function testInstantiationOfClass(): void
    {
        $this->expectException(\Klipper\Component\Resource\Exception\ClassNotInstantiableException::class);

        new ResourceStatutes();
    }
}
