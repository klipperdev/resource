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

/**
 * A object factory interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ObjectFactoryInterface
{
    /**
     * Create the object.
     *
     * @param string $classname The classname
     * @param array  $options   The options
     *
     * @return object
     */
    public function create(string $classname, array $options = []);
}
