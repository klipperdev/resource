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

use Symfony\Component\HttpFoundation\Request;

/**
 * A form config.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormConfig implements FormConfigInterface
{
    protected string $type;

    protected array $options;

    protected string $method;

    protected ?bool $clearMissing = null;

    protected string $converter;

    /**
     * @var callable[]
     */
    protected array $builderHandlers = [];

    /**
     * @param string $type      The class name of form type
     * @param array  $options   The form options for create the form type
     * @param string $method    The request method
     * @param string $converter The data converter for request content
     */
    public function __construct(
        string $type,
        array $options = [],
        string $method = Request::METHOD_POST,
        string $converter = 'json'
    ) {
        $this->setType($type);
        $this->setMethod($method);
        $this->setOptions($options);
        $this->setConverter($converter);
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setOptions(array $options): void
    {
        if (isset($options['method'])) {
            $this->setMethod($options['method']);
        }

        $this->options = array_merge($options, ['method' => $this->getMethod()]);
    }

    /**
     * @param null|mixed $object
     */
    public function getOptions($object = null): array
    {
        return $this->options;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
        $this->options['method'] = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setSubmitClearMissing(?bool $clearMissing): void
    {
        $this->clearMissing = $clearMissing;
    }

    public function getSubmitClearMissing(): bool
    {
        if (null === $this->clearMissing) {
            return Request::METHOD_PATCH !== $this->method;
        }

        return (bool) $this->clearMissing;
    }

    public function getConverter(): string
    {
        return $this->converter;
    }

    public function setConverter(string $converter): void
    {
        $this->converter = $converter;
    }

    public function getBuilderHandlers(): array
    {
        return $this->builderHandlers;
    }

    public function addBuilderHandler(?callable $builderHandler): self
    {
        $this->builderHandlers[] = $builderHandler;

        return $this;
    }
}
