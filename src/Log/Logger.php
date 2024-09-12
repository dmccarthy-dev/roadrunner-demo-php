<?php

namespace Roadrunnerdemo\Php\Log;

class Logger
{

    public function __construct()
    {
    }

    public function INFO($requestId, $data)
    {
        print_r($requestId . " - " . $data, false);
    }

}