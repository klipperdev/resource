<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Object;

use Klipper\Component\DefaultValue\ObjectFactoryInterface as DefaultValueObjectFactoryInterface;

/**
 * A object factory with Klipper Default Value.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DefaultValueObjectFactory implements ObjectFactoryInterface
{
    /**
     * @var DefaultValueObjectFactoryInterface
     */
    private $of;

    /**
     * Constructor.
     *
     * @param DefaultValueObjectFactoryInterface $of The default value object factory
     */
    public function __construct(DefaultValueObjectFactoryInterface $of)
    {
        $this->of = $of;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $classname, array $options = [])
    {
        return $this->of->create($classname, null, $options);
    }
}
