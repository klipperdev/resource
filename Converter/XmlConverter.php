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

use Klipper\Component\Resource\Exception\InvalidXmlConverterException;

/**
 * A xml request content converter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class XmlConverter extends AbstractConverter
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'xml';
    }

    /**
     * {@inheritdoc}
     */
    public function convert(string $content): array
    {
        try {
            $value = new \SimpleXMLElement($content);
            $value = json_encode($value);
            $value = json_decode($value, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidXmlConverterException();
            }
        } catch (\Throwable $e) {
            throw new InvalidXmlConverterException($this->translator->trans('converter.xml.invalid_body', [], 'KlipperResource'));
        }

        return \is_array($value) ? $value : [];
    }
}
