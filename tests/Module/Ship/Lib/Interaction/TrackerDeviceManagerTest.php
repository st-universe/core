<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\StuTestCase;

class TrackerDeviceManagerTest extends StuTestCase
{
    /** @var MockInterface|ShipSystemRepositoryInterface */
    private MockInterface $shipSystemRepository;
    /** @var MockInterface|PrivateMessageSenderInterface */
    private MockInterface $privateMessageSender;

    /** @var MockInterface|ShipWrapperInterface */
    private MockInterface $wrapper;
    /** @var MockInterface|ShipInterface */
    private MockInterface $ship;

    private TrackerDeviceManagerInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->shipSystemRepository = $this->mock(ShipSystemRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new TrackerDeviceManager(
            $this->shipSystemRepository,
            $this->privateMessageSender
        );
    }

    public function testDeactivateTrackerIfActiveExpectNothingWhenNoTracker(): void
    {
        $this->wrapper->shouldReceive('getTrackerSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->deactivateTrackerIfActive($this->wrapper, false);
    }

    public function testDeactivateTrackerIfActiveExpectNothingWhenNoTarget(): void
    {
        $trackerSystemData = $this->mock(TrackerSystemData::class);

        $this->wrapper->shouldReceive('getTrackerSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($trackerSystemData);

        $trackerSystemData->shouldReceive('getTargetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->deactivateTrackerIfActive($this->wrapper, false);
    }

    public function testDeactivateTrackerIfActiveExpectDeactivation(): void
    {
        $trackerSystemData = $this->mock(TrackerSystemData::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $targetShip = $this->mock(ShipInterface::class);
        $shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($targetShip);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $targetShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->wrapper->shouldReceive('getTrackerSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($trackerSystemData);
        $this->wrapper->shouldReceive('getShipSystemManager')
            ->withNoArgs()
            ->once()
            ->andReturn($shipSystemManager);

        $trackerSystemData->shouldReceive('getTargetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($targetWrapper);

        $shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TRACKER, true)
            ->once()
            ->andReturn($trackerSystemData);

        $this->subject->deactivateTrackerIfActive($this->wrapper, false);
    }
}
