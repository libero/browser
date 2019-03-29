<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use FluentDOM;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;

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
}
