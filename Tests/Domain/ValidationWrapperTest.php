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

use Klipper\Component\Resource\Domain\ValidationWrapper;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for Domain.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ValidationWrapperTest extends TestCase
{
    public function testGetValidationGroups(): void
    {
        $data = new \stdClass();
        $validationGroups = [
            'Default',
            'Test',
        ];
        $wrapper = new ValidationWrapper($data, $validationGroups);

        static::assertSame($data, $wrapper->getData());
        static::assertSame($validationGroups, $wrapper->getValidationGroups());
    }
}
