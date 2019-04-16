<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle;

use DOMNodeList;
use FluentDOM\DOM\Element;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Punic\Exception\InvalidLocale;
use Punic\Misc;
use UnexpectedValueException;
use function count;

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

    $uri = resolve_xml_base($element, $uri);

    if ('' === $uri->getScheme()) {
        throw new UnexpectedValueException('URI is not absolute');
    }

    return $uri;
}

function resolve_xml_base(Element $element, ?UriInterface $uri = null) : UriInterface
{
    /** @var DOMNodeList<Element> $ancestors */
    $ancestors = $element->ownerDocument->xpath()->evaluate('ancestor-or-self::*[@xml:base]', $element);

    for ($i = count($ancestors) - 1; $i >= 0; $i--) {
        $baseUri = new Uri($ancestors->item($i)->getAttribute('xml:base'));

        $uri = isset($uri) ? UriResolver::resolve($baseUri, $uri) : $baseUri;

        if ('' !== $uri->getScheme()) {
            break;
        }
    }

    return $uri;
}
