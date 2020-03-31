<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Domain;

/**
 * Wrapper data interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface WrapperInterface
{
    /**
     * Returns the wrapped data.
     *
     * @return mixed
     */
    public function getData();
}
