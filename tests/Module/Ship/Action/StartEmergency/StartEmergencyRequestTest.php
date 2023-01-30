<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartEmergency;

use MPScholten\RequestParser\NotFoundException;
use Stu\RequestTestCase;

/**
 * @extends RequestTestCase<StartEmergencyRequest>
 *
 * @runTestsInSeparateProcesses
 */
class StartEmergencyRequestTest extends RequestTestCase
{
    public function testGetEmergencyTestReturnsEmptyStringIfNotSet(): void
    {
        static::assertSame(
            '',
            $this->buildRequest()->getEmergencyText()
        );
    }


    public function testGetEmergencyTestReturnsSanitizedValue(): void
    {
        $value = '<fuu>unit tests are great!</fuu>';

        $_GET['text'] = $value;

        static::assertSame(
            strip_tags($value),
            $this->buildRequest()->getEmergencyText()
        );
    }

    public function testGetShipIdErorsIfNotSet(): void
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
        return StartEmergencyRequest::class;
    }
}
