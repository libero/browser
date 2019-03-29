<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Exception;

use InvalidArgumentException;
use Throwable;

class NoContentSet extends InvalidArgumentException
{
    public function __construct($message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function forPage(array $page, ?Throwable $previous = null) : NoContentSet
    {
        return new NoContentSet("No content has been added to {$page['type']} page '{$page['name']}'", $previous);
    }
}
