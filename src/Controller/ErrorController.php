<?php

declare(strict_types=1);

namespace Libero\Browser\Controller;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class ErrorController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke() : Response
    {
        $this->logger->debug('Some debug message', ['some context' => 'foo']);

        $this->logger->info('Some info message', ['some context' => 'bar']);

        $this->logger->notice('Some notice message');

        throw new RuntimeException('Some exception', 0, new RuntimeException('Some other exception'));
    }
}
