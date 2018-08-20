<?php
namespace Libero\ApiClientBundle\Services;

final class ReadData
{
    private const PATH = __DIR__.'/../data';

    public function send($data)
    {
        return file_get_contents(self::PATH."/".$data.".xml");
    }
}
