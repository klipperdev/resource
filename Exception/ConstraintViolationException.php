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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Base ConstraintViolationException for external constraint violations.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConstraintViolationException extends RuntimeException
{
    protected ConstraintViolationListInterface $violations;

    /**
     * @param ConstraintViolationListInterface $violations The constraint violations
     * @param string                           $message    The message of exception
     * @param int                              $code       The code of exception
     * @param \Throwable                       $previous   The previous exception
     */
    public function __construct(
        ConstraintViolationListInterface $violations,
        ?string $message = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message ?? Response::$statusTexts[400], $code, $previous);

        $this->violations = $violations;
    }

    /**
     * Get the constraint violations.
     */
    public function getConstraintViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
