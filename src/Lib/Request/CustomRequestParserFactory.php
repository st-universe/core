<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use MPScholten\RequestParser\Config;
use MPScholten\RequestParser\RequestParser;
use MPScholten\RequestParser\RequestParserFactory;
use Override;

final class CustomRequestParserFactory implements RequestParserFactory
{
    /** @var callable|Config */
    private $config;

    /**
     * @param array<mixed> $request
     */
    public function __construct(private array $request, callable|Config $config)
    {
        $this->config = $config;
    }

    #[Override]
    public function createQueryParser(): RequestParser
    {
        return new RequestParser(
            fn($parameterName) => $this->request[$parameterName] ?? null,
            $this->config
        );
    }

    #[Override]
    public function createBodyParser(): RequestParser
    {
        return new RequestParser(
            fn($parameterName) => $this->request[$parameterName] ?? null,
            $this->config
        );
    }
}
