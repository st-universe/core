<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use MPScholten\RequestParser\BaseControllerHelperTrait;

trait CustomControllerHelperTrait
{
    use BaseControllerHelperTrait;

    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $request = $_GET;
        } else {
            $request = $_POST;
        }
        $this->initRequestParser($request);
    }

    protected final function createRequestParserFactory($request, $config)
    {
        return new CustomRequestParserFactory($request, $config);
    }
}
