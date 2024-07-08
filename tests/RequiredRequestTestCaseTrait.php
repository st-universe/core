<?php

declare(strict_types=1);

namespace Stu;

use MPScholten\RequestParser\NotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;

trait RequiredRequestTestCaseTrait
{
    abstract public static function requiredRequestVarsDataProvider(): array;

    #[DataProvider('requiredRequestVarsDataProvider')]
    public function testRequiredRequestVars(
        string $methodName
    ): void {
        static::expectException(NotFoundException::class);

        call_user_func_array([$this->buildRequest(), $methodName], []);
    }
}
