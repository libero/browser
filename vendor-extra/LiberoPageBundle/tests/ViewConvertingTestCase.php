<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use LogicException;

trait ViewConvertingTestCase
{
    final protected function createDumpingConverter() : ViewConverter
    {
        return new CallbackViewConverter(
            function (NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View {
                return new View(
                    null,
                    ['node' => $node->getNodePath(), 'template' => $template, 'context' => $context]
                );
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
