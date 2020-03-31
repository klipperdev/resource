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

use Klipper\Component\Resource\Exception\InvalidArgumentException;
use Klipper\Component\Resource\Exception\UnexpectedTypeException;

/**
 * A request content converter manager interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ConverterRegistryInterface
{
    /**
     * Returns a converter by name.
     *
     * This methods registers the converter.
     *
     * @param string $name The name of the converter
     *
     * @throws UnexpectedTypeException  If the parameter is not a string
     * @throws InvalidArgumentException If the converter can not be retrieved
     *
     * @return ConverterInterface The converter
     */
    public function get(string $name): ConverterInterface;

    /**
     * Returns whether the given converter is supported.
     *
     * @param string $name The name of the converter
     *
     * @return bool Whether the type is supported
     */
    public function has(string $name): bool;
}
