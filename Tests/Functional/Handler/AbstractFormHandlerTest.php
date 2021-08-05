<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Functional\Handler;

use Klipper\Component\Resource\Converter\ConverterRegistry;
use Klipper\Component\Resource\Converter\JsonConverter;
use Klipper\Component\Resource\Handler\FormHandler;
use Klipper\Component\Resource\Handler\FormHandlerInterface;
use Klipper\Component\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;

/**
 * Abstract class for Functional tests for Form Handler.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractFormHandlerTest extends TestCase
{
    /**
     * Create form handler.
     *
     * @param null|Request $request The request for request stack
     * @param null|int     $limit   The limit
     *
     * @return FormHandlerInterface
     */
    protected function createFormHandler(?Request $request = null, ?int $limit = null)
    {
        $translator = new Translator('en');
        $ref = new \ReflectionClass(ResourceInterface::class);
        $translator->addResource('yaml', realpath(\dirname($ref->getFileName()).'/Resources/translations/KlipperResource.en.yaml'), 'en', 'KlipperResource');
        $translator->addLoader('yaml', new YamlFileLoader());

        $converterRegistry = new ConverterRegistry([
            new JsonConverter($translator),
        ]);

        $validator = Validation::createValidatorBuilder()
            ->addXmlMapping(__DIR__.'/../../Fixtures/config/validation.xml')
            ->getValidator()
        ;

        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory()
        ;

        $requestStack = new RequestStack();

        if (null !== $request) {
            $requestStack->push($request);
        }

        return new FormHandler($converterRegistry, $formFactory, $requestStack, $translator, $limit);
    }
}
