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
 * Base InvalidJsonConverterException for the converter of resource bundle.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InvalidJsonConverterException extends InvalidConverterException
{
    /**
     * @param string     $message  The exception message
     * @param int        $code     The exception code
     * @param \Throwable $previous The previous exception
     */
    public function __construct(string $message = 'Body should be a JSON object', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
