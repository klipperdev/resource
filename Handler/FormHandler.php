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

use Klipper\Component\Resource\Converter\ConverterRegistryInterface;
use Klipper\Component\Resource\Exception\InvalidArgumentException;
use Klipper\Component\Resource\Exception\InvalidResourceException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A form handler.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormHandler implements FormHandlerInterface
{
    protected ConverterRegistryInterface $converterRegistry;

    protected FormFactoryInterface $formFactory;

    protected Request $request;

    protected TranslatorInterface $translator;

    protected ?int $defaultLimit;

    protected ?int $maxLimit;

    /**
     * @param ConverterRegistryInterface $converterRegistry The converter registry
     * @param FormFactoryInterface       $formFactory       The form factory
     * @param RequestStack               $requestStack      The request stack
     * @param TranslatorInterface        $translator        The translator
     * @param null|int                   $defaultLimit      The limit of max data rows
     * @param null|int                   $maxLimit          The max limit of max data rows
     *
     * @throws InvalidArgumentException When the current request is request stack is empty
     */
    public function __construct(
        ConverterRegistryInterface $converterRegistry,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        ?int $defaultLimit = null,
        ?int $maxLimit = null
    ) {
        $request = $requestStack->getCurrentRequest();

        if (null === $request) {
            throw new InvalidArgumentException('The current request is required in request stack');
        }

        $this->converterRegistry = $converterRegistry;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->translator = $translator;
        $this->defaultLimit = $this->validateLimit($defaultLimit);
        $this->maxLimit = $this->validateLimit($maxLimit);
    }

    public function processForm(FormConfigInterface $config, $object): FormInterface
    {
        $forms = $this->process($config, [$object]);

        return $forms[0];
    }

    public function processForms(FormConfigListInterface $config, array $objects = []): array
    {
        return $this->process($config, $objects);
    }

    public function getDefaultLimit(): ?int
    {
        return $this->defaultLimit;
    }

    public function getMaxLimit(): ?int
    {
        return $this->maxLimit ?? $this->defaultLimit;
    }

    /**
     * Get the data list and objects.
     *
     * @param FormConfigInterface $config  The form config
     * @param array[]|object[]    $objects The list of object instance
     */
    protected function getDataListObjects(FormConfigInterface $config, array $objects): array
    {
        $limit = $this->getLimit($config instanceof FormConfigListInterface ? $config->getLimit() : null);
        $dataList = $this->getDataList($config);

        if (null !== $limit && \count($dataList) > $limit) {
            $msg = $this->translator->trans('form_handler.size_exceeds', [
                '{{ limit }}' => $limit,
            ], 'KlipperResource');

            throw new InvalidResourceException(sprintf($msg, $limit));
        }

        if ($config instanceof FormConfigListInterface && 0 === \count($objects)) {
            $objects = $config->convertObjects($dataList);
        }

        $dataList = array_values($dataList);
        $objects = array_values($objects);

        return [$dataList, $objects];
    }

    /**
     * Get the form data list.
     *
     * @param FormConfigInterface $config The form config
     */
    protected function getDataList(FormConfigInterface $config): array
    {
        $converter = $this->converterRegistry->get($config->getConverter());
        $dataList = $converter->convert((string) $this->request->getContent());

        if ($config instanceof FormConfigListInterface) {
            try {
                $dataList = $config->findList($dataList);
            } catch (InvalidResourceException $e) {
                throw new InvalidResourceException($this->translator->trans('form_handler.results_field_required', [], 'KlipperResource'));
            }
        } else {
            $dataList = [$dataList];
        }

        return $dataList;
    }

    /**
     * Get the limit.
     *
     * @param null|int $limit The limit
     *
     * @return null|int Returns null for unlimited row or a integer greater than 1
     */
    protected function getLimit(?int $limit = null): ?int
    {
        $max = $this->getMaxLimit();
        $limit = $limit ?? $this->getDefaultLimit();
        $limit = null !== $limit && null !== $max ? min($max, $limit) : $limit;

        return $this->validateLimit($limit);
    }

    /**
     * Validate the limit with a integer greater than 1.
     *
     * @param null|int $limit The limit
     */
    protected function validateLimit(?int $limit): ?int
    {
        return null === $limit
            ? null
            : max(1, $limit);
    }

    /**
     * Create the list of form for the object instances.
     *
     * @param FormConfigInterface $config  The form config
     * @param array[]|object[]    $objects The list of object instance
     *
     * @throws InvalidResourceException When the size if request data and the object instances is different
     *
     * @return FormInterface[]
     */
    private function process(FormConfigInterface $config, array $objects): array
    {
        list($dataList, $objects) = $this->getDataListObjects($config, $objects);
        $builderHandlers = $config->getBuilderHandlers();
        $forms = [];

        if (\count($objects) !== \count($dataList)) {
            $msg = $this->translator->trans('form_handler.different_size_request_list', [
                '{{ requestSize }}' => \count($dataList),
                '{{ objectSize }}' => \count($objects),
            ], 'KlipperResource');

            throw new InvalidResourceException($msg);
        }

        foreach ($objects as $i => $object) {
            $formBuilder = $this->formFactory->createBuilder($config->getType(), $object, $config->getOptions($object));

            foreach ($builderHandlers as $builderHandler) {
                $builderHandler($formBuilder);
            }

            $form = $formBuilder->getForm();
            $form->submit($dataList[$i], $config->getSubmitClearMissing());
            $forms[] = $form;
        }

        return $forms;
    }
}
