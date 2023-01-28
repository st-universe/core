<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopEmergency;

use MPScholten\RequestParser\NotFoundException;
use Stu\RequestTestCase;

/**
 * @extends RequestTestCase<StopEmergencyRequest>
 *
 * @runTestsInSeparateProcesses
 */
class StopEmergencyRequestTest extends RequestTestCase
{
    public function testGetShipIdErrorsIfNotSet(): void
    {
        static::expectException(NotFoundException::class);

        $this->buildRequest()->getShipId();
    }

    public function testGetShipIdReturnsValue(): void
    {
        $shipId = 666;

        $_GET['id'] = (string) $shipId;

        static::assertSame(
            $shipId,
            $this->buildRequest()->getShipId()
        );
    }

    protected function getRequestClass(): string
    {
        return StopEmergencyRequest::class;
    }
}
