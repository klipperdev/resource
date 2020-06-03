<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Tests\Converter;

use Klipper\Component\Resource\Converter\ConverterInterface;
use Klipper\Component\Resource\Converter\JsonConverter;
use Klipper\Component\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Tests case for json converter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class JsonConverterTest extends TestCase
{
    protected ?ConverterInterface $converter = null;

    protected function setUp(): void
    {
        $translator = new Translator('en');
        $ref = new \ReflectionClass(ResourceInterface::class);
        $translator->addResource('xml', realpath(\dirname($ref->getFileName()).'/Resources/translations/KlipperResource.en.xlf'), 'en', 'KlipperResource');
        $translator->addLoader('xml', new XliffFileLoader());

        $this->converter = new JsonConverter($translator);
    }

    public function testBasic(): void
    {
        static::assertSame('json', $this->converter->getName());
    }

    public function testInvalidConversion(): void
    {
        $this->expectException(\Klipper\Component\Resource\Exception\InvalidConverterException::class);
        $this->expectExceptionMessage('Request body should be a JSON object');

        $this->converter->convert('<xml>content</xml>');
    }

    public function testConversion(): void
    {
        $content = $this->converter->convert('{"foo": "bar"}');

        static::assertEquals(['foo' => 'bar'], $content);
    }
}
