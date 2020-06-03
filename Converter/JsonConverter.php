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

use Klipper\Component\Resource\Exception\InvalidJsonConverterException;

/**
 * A request content converter interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JsonConverter extends AbstractConverter
{
    public function getName(): string
    {
        return 'json';
    }

    public function convert(string $content): array
    {
        try {
            $value = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidJsonConverterException($this->translator->trans('converter.json.invalid_body', [], 'KlipperResource'));
        }

        return \is_array($value) ? $value : [];
    }
}
