<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Domain;

use Klipper\Component\Resource\Domain\Wrapper;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for Domain.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class WrapperTest extends TestCase
{
    public function testGetData(): void
    {
        $data = new \stdClass();
        $wrapper = new Wrapper($data);

        static::assertSame($data, $wrapper->getData());
    }
}
