<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use LogicException;

trait ViewConvertingTestCase
{
    final protected function createDumpingConverter() : ViewConverter
    {
        return new CallbackViewConverter(
            function (Element $object, ?string $template, array $context) : View {
                $arguments = [
                    'element' => $object->getNodePath(),
                    'template' => $template,
                    'context' => $context,
                ];

                return new View(null, $arguments);
            }
        );
    }

    final protected function createFailingConverter() : ViewConverter
    {
        return new CallbackViewConverter(
            function () : View {
                throw new LogicException('Not expected to be used');
            }
        );
    }
}
