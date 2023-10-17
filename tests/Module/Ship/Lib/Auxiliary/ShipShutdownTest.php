<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Auxiliary;

use Mockery\MockInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipShutdownTest extends StuTestCase
{
    /** @var MockInterface|ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface|ShipSystemManagerInterface */
    private MockInterface $shipSystemManager;

    /** @var MockInterface|LeaveFleetInterface */
    private MockInterface $leaveFleet;

    /** @var MockInterface|ShipStateChangerInterface */
    private MockInterface $shipStateChanger;

    /** @var MockInterface|ShipUndockingInterface */
    private MockInterface $shipUndocking;

    private ShipShutdownInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->shipStateChanger = $this->mock(ShipStateChangerInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);

        $this->subject = new ShipShutdown(
            $this->shipRepository,
            $this->shipSystemManager,
            $this->leaveFleet,
            $this->shipStateChanger,
            $this->shipUndocking
        );
    }

    public function testShutdown(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('setAlertStateGreen')
            ->withNoArgs()
            ->once();
        $ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();

        $this->shipSystemManager->shouldReceive('deactivateAll')
            ->with($wrapper)
            ->once();

        $this->leaveFleet->shouldReceive('leaveFleet')
            ->with($ship)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($ship)
            ->once();

        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($wrapper, ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($ship)
            ->once();

        $this->subject->shutdown($wrapper);
    }
}
