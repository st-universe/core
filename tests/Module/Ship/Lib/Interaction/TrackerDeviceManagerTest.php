<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Mockery\MockInterface;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\StuTestCase;

class TrackerDeviceManagerTest extends StuTestCase
{
    /** @var MockInterface|ShipSystemRepositoryInterface */
    private MockInterface $shipSystemRepository;

    /** @var MockInterface|ShipWrapperInterface */
    private MockInterface $wrapper;
    /** @var MockInterface|ShipInterface */
    private MockInterface $ship;

    private TrackerDeviceManagerInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->shipSystemRepository = $this->mock(ShipSystemRepositoryInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new TrackerDeviceManager(
            $this->shipSystemRepository
        );
    }

    public function testDeactivateTrackerIfExisting(): void
    {
        $systemTracker = $this->mock(ShipSystemInterface::class);
        $trackerSystemData = $this->mock(TrackerSystemData::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TRACKER)
            ->once()
            ->andReturnTrue();
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TRACKER)
            ->once()
            ->andReturn($systemTracker);
        $systemTracker->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();
        $trackerSystemData->shouldReceive('setTarget')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $trackerSystemData->shouldReceive('update')
            ->withNoArgs()
            ->once();
        $this->wrapper->shouldReceive('getTrackerSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($trackerSystemData);

        $this->subject->deactivateTrackerIfExisting($this->wrapper);
    }
}
