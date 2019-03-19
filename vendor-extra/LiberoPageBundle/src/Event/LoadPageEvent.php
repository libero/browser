<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Event;

use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use function GuzzleHttp\Promise\all;

final class LoadPageEvent extends Event
{
    public const NAME = 'libero.page.load';

    private $context;
    private $documents = [];
    private $request;

    public function __construct(Request $request, array $context = [])
    {
        $this->request = $request;
        $this->context = $context;
    }

    public function getRequest() : Request
    {
        return $this->request;
    }

    public function addDocument(string $key, PromiseInterface $promise) : void
    {
        $this->documents[$key] = $promise;
    }

    public function getDocuments() : PromiseInterface
    {
        return all($this->documents);
    }

    public function getContext() : array
    {
        return $this->context;
    }

    public function setContext(string $key, $value) : void
    {
        $this->context[$key] = $value;
    }
}
