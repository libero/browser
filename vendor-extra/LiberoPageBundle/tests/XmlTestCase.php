<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use FluentDOM;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;

trait XmlTestCase
{
    final protected function loadDocument(string $xml) : Document
    {
        $xml = FluentDOM::load($xml);
        $xml->xpath()->registerNamespace('libero', 'http://libero.pub');
        $xml->xpath()->registerNamespace('jats', 'http://jats.nlm.nih.gov');

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
