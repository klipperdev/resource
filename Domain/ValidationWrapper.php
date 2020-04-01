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

use Symfony\Component\Validator\Constraints\GroupSequence;

/**
 * Validation wrapper data.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ValidationWrapper extends Wrapper implements ValidationWrapperInterface
{
    /**
     * @var GroupSequence[]|string[]|string[][]
     */
    protected $validationGroups;

    /**
     * Constructor.
     *
     * @param mixed                               $data             The wrapped data
     * @param GroupSequence[]|string[]|string[][] $validationGroups The validation groups
     */
    public function __construct($data, array $validationGroups)
    {
        parent::__construct($data);

        $this->validationGroups = $validationGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }
}
