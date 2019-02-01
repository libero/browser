<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\Handler;

use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Libero\ViewsBundle\Views\ViewConverter;
use UnexpectedValueException;
use function array_merge;

final class LiberoContentHandler implements ContentHandler
{
    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function handle(Element $documentElement, array $context) : array
    {
        /** @var Document $document */
        $document = $documentElement->ownerDocument;
        $xpath = $document->xpath();
        $xpath->registerNamespace('libero', 'http://libero.pub');

        $front = $xpath->firstOf('/libero:item/libero:front[1]');

        if (!$front instanceof Element) {
            throw new UnexpectedValueException('Could not find a front');
        }

        $contentHeader = $this->converter->convert($front, '@LiberoPatterns/content-header.html.twig', $context);

        return array_merge(
            $context,
            [
                'title' => $contentHeader->getArgument('contentTitle')['text'],
                'content' => [$contentHeader],
            ]
        );
    }
}
