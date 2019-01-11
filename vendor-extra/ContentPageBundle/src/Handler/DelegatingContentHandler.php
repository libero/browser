<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Handler;

use FluentDOM\DOM\Element;
use UnexpectedValueException;
use function sprintf;

final class DelegatingContentHandler implements ContentHandler
{
    private $handlers;

    /**
     * @param iterable<ContentHandler> $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle(Element $documentElement, array $context) : array
    {
        foreach ($this->handlers as $handler) {
            $result = $handler->handle($documentElement, $context);

            if ([] === $result) {
                continue;
            }

            return $result;
        }

        throw new UnexpectedValueException(
            sprintf('Unable to handle {%s}%s', $documentElement->namespaceURI, $documentElement->localName)
        );
    }
}
