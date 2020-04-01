<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Converter;

use Klipper\Component\Resource\Exception\InvalidConverterException;

/**
 * A request content converter interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ConverterInterface
{
    /**
     * Get the name of the conversion.
     */
    public function getName(): string;

    /**
     * Convert the string content to array.
     *
     * @throws InvalidConverterException When the data can not be converted
     */
    public function convert(string $content): array;
}
