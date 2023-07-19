<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use MPScholten\RequestParser\BaseControllerHelperTrait;
use MPScholten\RequestParser\Config;

trait CustomControllerHelperTrait
{
    use BaseControllerHelperTrait;

    public function __construct()
    {
        $request = $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
        $this->initRequestParser($request);
    }

    /**
     * @param array<mixed> $request
     * @param callable|Config $config
     */
    final protected function createRequestParserFactory($request, $config): CustomRequestParserFactory
    {
        return new CustomRequestParserFactory($request, $config);
    }

    protected function tidyString(string $string): string
    {
        return trim(
            str_replace(
                ['<', '>', '&gt;', '&lt;'],
                '',
                strip_tags($string)
            )
        );
    }
}
