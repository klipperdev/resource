<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Exception;

/**
 * Base UnexpectedTypeException for the resource bundle.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UnexpectedTypeException extends InvalidArgumentException
{
    /**
     * @param mixed    $value        The value given
     * @param string   $expectedType The expected type
     * @param null|int $position     The position in list
     */
    public function __construct($value, string $expectedType, ?int $position = null)
    {
        $msg = sprintf('Expected argument of type "%s", "%s" given', $expectedType, \is_object($value) ? \get_class((object) $value) : \gettype($value));

        if (null !== $position) {
            $msg .= sprintf(' at the position "%s"', $position);
        }

        parent::__construct($msg);
    }
}
