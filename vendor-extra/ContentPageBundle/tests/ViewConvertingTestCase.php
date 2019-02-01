<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\CallbackInlineViewConverter;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\InlineViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use LogicException;

trait ViewConvertingTestCase
{
    final protected function createConverter(bool $includeTemplate = false) : ViewConverter
    {
        return new CallbackViewConverter(
            function (Element $object, ?string $template, array $context) use ($includeTemplate) : View {
                $arguments = [
                    'element' => $object->getNodePath(),
                    'context' => $context,
                ];

                if ($includeTemplate) {
                    $arguments['template'] = $template;
                }

                return new View($template, $arguments);
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

    final protected function createInlineConverter() : InlineViewConverter
    {
        return new CallbackInlineViewConverter(
            function (NonDocumentTypeChildNode $object, array $context) : View {
                $arguments = [
                    'object' => $object->getNodePath(),
                    'context' => $context,
                ];

                return new View('child', $arguments);
            }
        );
    }

    final protected function createFailingInlineConverter() : InlineViewConverter
    {
        return new CallbackInlineViewConverter(
            function () : View {
                throw new LogicException('Not expected to be used');
            }
        );
    }
}
