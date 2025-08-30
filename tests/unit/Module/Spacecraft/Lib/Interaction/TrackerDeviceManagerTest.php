<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\Data\TrackerSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

class TrackerDeviceManagerTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemRepositoryInterface $shipSystemRepository;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;

    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;

    private TrackerDeviceManagerInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->shipSystemRepository = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);

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
        $targetShip = $this->mock(Ship::class);
        $spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $user = $this->mock(User::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);

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
        $this->wrapper->shouldReceive('getSpacecraftSystemManager')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraftSystemManager);

        $trackerSystemData->shouldReceive('getTargetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($targetWrapper);

        $spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TRACKER, true)
            ->once()
            ->andReturn($trackerSystemData);

        $this->subject->deactivateTrackerIfActive($this->wrapper, false);
    }
}
