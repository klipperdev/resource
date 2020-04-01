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

use Symfony\Component\Form\FormInterface;

/**
 * A form handler interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FormHandlerInterface
{
    /**
     * Process form for one object instance (create and submit form).
     *
     * @param FormConfigInterface $config The form config
     * @param array|object        $object The object instance
     */
    public function processForm(FormConfigInterface $config, $object): FormInterface;

    /**
     * Process form for one object instance (create and submit form).
     *
     * @param FormConfigListInterface $config  The form config
     * @param array[]|object[]        $objects The list of object instance
     *
     * @return FormInterface[]
     */
    public function processForms(FormConfigListInterface $config, array $objects = []): array;

    /**
     * Get the default limit. If the value is null, then there is not limit of quantity of rows.
     */
    public function getDefaultLimit(): ?int;

    /**
     * Get the max limit. If the value is null, then there is not limit of quantity of rows.
     */
    public function getMaxLimit(): ?int;
}
