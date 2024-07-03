<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use Override;
use MPScholten\RequestParser\Config;
use MPScholten\RequestParser\RequestParser;
use MPScholten\RequestParser\RequestParserFactory;

final class CustomRequestParserFactory implements RequestParserFactory
{
    private $config;

    /**
     * @param array<mixed> $request
     * @param callable|Config $config
     */
    public function __construct(private array $request, $config)
    {
        $this->config = $config;
    }

    #[Override]
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

    #[Override]
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
