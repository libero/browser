<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle;

use FluentDOM\DOM\Element;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Punic\Exception\InvalidLocale;
use Punic\Misc;
use UnexpectedValueException;

const DIRECTION_LTR = 'ltr';
const DIRECTION_RTL = 'rtl';

function text_direction(string $locale) : string
{
    try {
        return 'right-to-left' === Misc::getCharacterOrder($locale) ? DIRECTION_RTL : DIRECTION_LTR;
    } catch (InvalidLocale $exception) {
        throw new InvalidArgumentException($exception->getMessage(), 0, $exception);
    }
}

function absolute_xlink_href(Element $element) : UriInterface
{
    $uri = new Uri($element->getAttributeNS('http://www.w3.org/1999/xlink', 'href'));

    if ('' !== $uri->getScheme()) {
        return $uri;
    }

    $uri = UriResolver::resolve(new Uri($element->baseURI), $uri);

    if ('' === $uri->getScheme()) {
        throw new UnexpectedValueException('URI is not absolute');
    }

    return $uri;
}
