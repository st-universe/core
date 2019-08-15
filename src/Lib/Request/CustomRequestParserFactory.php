<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use MPScholten\RequestParser\RequestParser;
use MPScholten\RequestParser\RequestParserFactory;

final class CustomRequestParserFactory implements RequestParserFactory
{
    /**
     * @var array
     */
    private $request;

    private $config;

    public function __construct(array $request, $config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    public function createQueryParser()
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

    public function createBodyParser()
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
