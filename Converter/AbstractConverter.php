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

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractConverter implements ConverterInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator The translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
