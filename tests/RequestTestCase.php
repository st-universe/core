<?php

declare(strict_types=1);

namespace Stu;

use PHPUnit\Framework\Attributes\DataProvider;

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

    #[\Override]
    protected function tearDown(): void
    {
        // reset vars
        $_GET = [];
        $_POST = [];
    }

    abstract public static function requestVarsDataProvider(): array;

    #[DataProvider('requestVarsDataProvider')]
    public function testRequestVars(
        string $methodName,
        string $paramName,
        $testValue,
        mixed $expectedValue
    ): void {
        if ($testValue !== null) {
            $_GET[$paramName] = $testValue;
        }

        static::assertSame(
            $expectedValue,
            call_user_func_array([$this->buildRequest(), $methodName], [])
        );
    }
}
