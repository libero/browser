<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use FluentDOM\DOM\Attribute;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\EmptyView;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use LogicException;

trait ViewConvertingTestCase
{
    final protected function createDumpingConverter() : ViewConverter
    {
        return new CallbackViewConverter(
            static function (NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View {
                return new TemplateView(
                    '',
                    ['node' => $node->getNodePath(), 'template' => $template, 'context' => $context]
                );
            }
        );
    }

    final protected function createFailingConverter() : ViewConverter
    {
        return new CallbackViewConverter(
            static function () : View {
                throw new LogicException('Not expected to be used');
            }
        );
    }

    final protected function createFilteringConverter(ViewConverter $converter, callable $filter) : ViewConverter
    {
        return new CallbackViewConverter(
            static function (
                NonDocumentTypeChildNode $node,
                ?string $template = null,
                array $context = []
            ) use (
                $converter,
                $filter
            ) : View {
                if (false === $filter($node, $template, $context)) {
                    return new EmptyView($context);
                }

                return $converter->convert($node, $template, $context);
            }
        );
    }

    final protected function createLanguageAddingConverter(ViewConverter $converter) : ViewConverter
    {
        return new CallbackViewConverter(
            static function (
                NonDocumentTypeChildNode $node,
                ?string $template = null,
                array $context = []
            ) use (
                $converter
            ) : View {
                $view = $converter->convert($node, $template, $context);

                if (!$view instanceof TemplateView || !$node instanceof Element) {
                    return $view;
                }

                $lang = $node->ownerDocument->xpath()
                    ->firstOf('ancestor-or-self::*[@xml:lang][1]/@xml:lang', $node);

                if (!$lang instanceof Attribute) {
                    return $view;
                }

                return $view->withContext(['lang' => $lang->nodeValue]);
            }
        );
    }
}
