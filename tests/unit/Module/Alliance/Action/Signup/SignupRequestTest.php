<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class SignupRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[\Override]
    protected function getRequestClass(): string
    {
        return SignupRequest::class;
    }

    #[\Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getAllianceId', 'id', '666', 666],
        ];
    }

    #[\Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getAllianceId'],
        ];
    }
}
