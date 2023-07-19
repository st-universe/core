<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use MPScholten\RequestParser\Config;
use MPScholten\RequestParser\RequestParser;
use MPScholten\RequestParser\RequestParserFactory;

final class CustomRequestParserFactory implements RequestParserFactory
{
    /** @var array<mixed> */
    private array $request;

    private $config;

    /**
     * @param array<mixed> $request
     * @param callable|Config $config
     */
    public function __construct(array $request, $config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    public function createQueryParser(): RequestParser
    {
        return new RequestParser(
            function ($parameterName) {
                if (isset($this->request[$parameterName])) {
                    return $this->request[$parameterName];
                }
                return null;
            },
            $this->config
        );
    }

    public function createBodyParser(): RequestParser
    {
        return new RequestParser(
            function ($parameterName) {
                if (isset($this->request[$parameterName])) {
                    return $this->request[$parameterName];
                }
                return null;
            },
            $this->config
        );
    }
}
