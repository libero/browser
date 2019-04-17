<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use FluentDOM;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;

trait XmlTestCase
{
    final protected function loadDocument(string $xml, ?string $documentUri = null) : Document
    {
        $xml = FluentDOM::load($xml);
        $xml->documentURI = $documentUri;
        $xml->xpath()->registerNamespace('libero', 'http://libero.pub');
        $xml->xpath()->registerNamespace('jats', 'http://jats.nlm.nih.gov');
        $xml->xpath()->registerNamespace('xlink', 'http://www.w3.org/1999/xlink');

        return $xml;
    }

    final protected function loadElement(string $xml) : Element
    {
        return $this->loadDocument($xml)->documentElement;
    }

    final protected function loadNode(string $xml) : NonDocumentTypeChildNode
    {
        return $this->loadElement("<root>$xml</root>")[0];
    }
}
