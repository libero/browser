<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle;

use FluentDOM;
use FluentDOM\DOM\Element;

trait XmlTestCase
{
    final protected function loadXml(string $xml) : Element
    {
        $xml = FluentDOM::load($xml);
        $xml->xpath()->registerNamespace('libero', 'http://libero.pub');
        $xml->xpath()->registerNamespace('jats', 'http://jats.nlm.nih.gov');

        return $xml->documentElement;
    }
}
