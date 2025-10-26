<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<AcceptApplicationRequest>
 */
class AcceptApplicationRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[\Override]
    protected function getRequestClass(): string
    {
        return AcceptApplicationRequest::class;
    }

    #[\Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getApplicationId', 'aid', '666', 666],
        ];
    }

    #[\Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getApplicationId'],
        ];
    }
}
