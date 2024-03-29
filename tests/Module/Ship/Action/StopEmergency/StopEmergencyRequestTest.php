<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopEmergency;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<StopEmergencyRequest>
 *
 * @runTestsInSeparateProcesses
 */
class StopEmergencyRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return StopEmergencyRequest::class;
    }

    public static function requestVarsDataProvider(): array
    {
        return [
            ['getShipId', 'id', '666', 666],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getShipId'],
        ];
    }
}
