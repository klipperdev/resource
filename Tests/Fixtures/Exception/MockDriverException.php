<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Fixtures\Exception;

use Doctrine\DBAL\Driver\Exception;

/**
 * Mock driver exception.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockDriverException extends \Exception implements Exception
{
    public function getErrorCode(): int
    {
        return 4224;
    }

    public function getSQLState(): ?string
    {
        return null;
    }
}
