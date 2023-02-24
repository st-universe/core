<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeclineApplication;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class DeclineApplicationRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return DeclineApplicationRequest::class;
    }

    public static function requestVarsDataProvider(): array
    {
        return [
            ['getApplicationId', 'aid', '666', 666],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getApplicationId'],
        ];
    }
}
