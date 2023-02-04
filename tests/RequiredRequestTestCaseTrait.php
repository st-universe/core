<?php

declare(strict_types=1);

namespace Stu;

use MPScholten\RequestParser\NotFoundException;

trait RequiredRequestTestCaseTrait
{
    abstract public function requiredRequestVarsDataProvider(): array;

    /**
     * @dataProvider requiredRequestVarsDataProvider
     */
    public function testRequiredRequestVars(
        string $methodName
    ): void {
        static::expectException(NotFoundException::class);

        call_user_func_array([$this->buildRequest(), $methodName], []);
    }
}
