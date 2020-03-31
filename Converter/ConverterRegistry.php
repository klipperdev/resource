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
class ConverterRegistry implements ConverterRegistryInterface
{
    /**
     * @var ConverterInterface[]
     */
    protected $converters = [];

    /**
     * Constructor.
     *
     * @param ConverterInterface[] $converters
     *
     * @throws UnexpectedTypeException When the converter is not an instance of "Klipper\Component\Resource\Converter\ConverterInterface"
     */
    public function __construct(array $converters)
    {
        foreach ($converters as $converter) {
            if (!$converter instanceof ConverterInterface) {
                throw new UnexpectedTypeException($converter, ConverterInterface::class);
            }
            $this->converters[strtolower($converter->getName())] = $converter;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): ConverterInterface
    {
        $sName = strtolower($name);

        if (isset($this->converters[$sName])) {
            return $this->converters[$sName];
        }

        throw new InvalidArgumentException(sprintf('Could not load content converter "%s"', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->converters[strtolower($name)]);
    }
}
