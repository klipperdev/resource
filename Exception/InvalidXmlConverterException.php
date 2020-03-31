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
 * Base InvalidXmlConverterException for the converter of resource bundle.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InvalidXmlConverterException extends InvalidConverterException
{
    /**
     * Constructor.
     *
     * @param string     $message  The exception message
     * @param int        $code     The exception code
     * @param \Exception $previous The previous exception
     */
    public function __construct(string $message = 'Body should be a XML object', int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
