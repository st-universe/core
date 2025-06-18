<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use MPScholten\RequestParser\BaseControllerHelperTrait;
use MPScholten\RequestParser\Config;
use MPScholten\RequestParser\TypeParser;
use request;

trait CustomControllerHelperTrait
{
    use BaseControllerHelperTrait;

    public function __construct()
    {
        if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
            $request = $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
            $this->initRequestParser($request);
        }
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

    protected function parameter(string $name): TypeParser
    {
        if (request::isMocked()) {
            $this->initRequestParser(request::getvars());
        }

        return $this->queryParameter($name);
    }
}
