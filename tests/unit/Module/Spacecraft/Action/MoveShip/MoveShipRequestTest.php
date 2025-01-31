<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\MoveShip;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class MoveShipRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return MoveShipRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getShipId', 'id', '666', 666],
            ['getFieldCount', 'navapp', '3', 3],
            ['getFieldCount', 'navapp', '3', 3],
            ['getFieldCount', 'navapp', -1, 1],
            ['getFieldCount', 'navapp', 10, 1],
            ['getDestinationPosX', 'posx', '42', 42],
            ['getDestinationPosY', 'posy', '42', 42],
        ];
    }

    #[Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getShipId'],
            ['getDestinationPosX'],
            ['getDestinationPosY'],
        ];
    }
}
