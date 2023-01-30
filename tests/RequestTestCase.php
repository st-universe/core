<?php

declare(strict_types=1);

namespace Stu;

/**
 * @template TRequestClass of object
 */
abstract class RequestTestCase extends StuTestCase
{
    /**
     * @return TRequestClass
     */
    protected function buildRequest(
        string $requestMethod = 'GET'
    ): object {
        $_SERVER['REQUEST_METHOD'] = $requestMethod;

        $requestClass = $this->getRequestClass();

        return new $requestClass();
    }

    /**
     * @return class-string<TRequestClass>
     */
    abstract protected function getRequestClass(): string;
}
