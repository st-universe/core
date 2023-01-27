<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Module\Ship\Lib\Movement\Component\ShipMovementBlockingDeterminator;
use Stu\StuTestCase;

class ShipMovementComponentsFactoryTest extends StuTestCase
{
    private ShipMovementComponentsFactory $subject;

    protected function setUp(): void
    {
        $this->subject = new ShipMovementComponentsFactory();
    }

    public function testCreateShipMovementBlockingDeterminatorReturnsInstance(): void
    {
        $this->assertInstanceOf(
            ShipMovementBlockingDeterminator::class,
            $this->subject->createShipMovementBlockingDeterminator()
        );
    }
}
