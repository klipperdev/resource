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
 * Base ClassNotInstantiableException for the resource bundle.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ClassNotInstantiableException extends RuntimeException
{
    public function __construct(string $classname)
    {
        parent::__construct(sprintf('The "%s" class cannot be instantiated', $classname));
    }
}
