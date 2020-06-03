<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Handler;

/**
 * A form config list for closure converter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ClosureFormConfigList extends FormConfigList
{
    protected ?\Closure $objectConverter = null;

    public function setObjectConverter(\Closure $converter): self
    {
        $this->objectConverter = $converter;

        return $this;
    }

    public function convertObjects(array &$list): array
    {
        if ($this->objectConverter instanceof \Closure) {
            $converter = $this->objectConverter;

            return $converter($list);
        }

        return [];
    }
}
