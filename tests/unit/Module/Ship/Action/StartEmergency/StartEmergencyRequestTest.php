<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartEmergency;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<StartEmergencyRequest>
 */
class StartEmergencyRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return StartEmergencyRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getShipId', 'id', '666', 666],
            ['getEmergencyText', 'text', '<foo>bar</foo>', 'bar'],
            ['getEmergencyText', 'text', null, ''],
        ];
    }

    #[Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getShipId'],
        ];
    }
}
