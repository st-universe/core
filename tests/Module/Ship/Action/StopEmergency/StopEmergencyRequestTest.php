<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopEmergency;

use Override;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<StopEmergencyRequest>
 *
 */
#[RunTestsInSeparateProcesses]
class StopEmergencyRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return StopEmergencyRequest::class;
    }

    #[Override]
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
