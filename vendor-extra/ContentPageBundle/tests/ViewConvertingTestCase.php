<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;

trait ViewConvertingTestCase
{
    final protected function createConverter() : ViewConverter
    {
        return new CallbackViewConverter(
            function (Element $object, ?string $template, array $context) : View {
                return new View($template, ['element' => $object->getNodePath(), 'context' => $context]);
            }
        );
    }
}
