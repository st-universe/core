<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<SalvageEmergencyPodsRequest>
 */
class SalvageEmergencyPodsRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return SalvageEmergencyPodsRequest::class;
    }

    public static function requestVarsDataProvider(): array
    {
        return [
            ['getShipId', 'id', '666', 666],
            ['getTargetId', 'target', '42', 42]
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getShipId', 'getTargetId'],
        ];
    }
}
