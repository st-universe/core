<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Component;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\Control\StuRandom;
use Stu\Module\Prestige\Lib\PrestigeCalculationInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertedShipsDetectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class TrapDetectionTest extends StuTestCase
{
    private MockInterface&AlertedShipsDetectionInterface $alertedShipsDetection;
    private MockInterface&PrestigeCalculationInterface $prestigeCalculation;
    private MockInterface&StuRandom $stuRandom;

    private MockInterface&LocationInterface $location;
    private MockInterface&ShipInterface $leadShip;
    private MockInterface&UserInterface $user;

    private TrapDetectionInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->alertedShipsDetection = $this->mock(AlertedShipsDetectionInterface::class);
        $this->prestigeCalculation = $this->mock(PrestigeCalculationInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->location = $this->mock(LocationInterface::class);
        $this->leadShip = $this->mock(ShipInterface::class);
        $this->user = $this->mock(UserInterface::class);

        $this->leadShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->subject = new TrapDetection(
            $this->alertedShipsDetection,
            $this->prestigeCalculation,
            $this->stuRandom
        );
    }

    public function testIsAlertTrapExpectFalseWhenNoAlertedShipsOnLocation(): void
    {
        $this->alertedShipsDetection->shouldReceive('getAlertedShipsOnLocation')
            ->with($this->location, $this->user)
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->subject->isAlertTrap($this->location, $this->leadShip);

        $this->assertFalse($result);
    }
    public function testIsAlertTrapExpectFalseWhenAlertedPrestigeOnly3TimesOfPirates(): void
    {
        $wrapper1 = $this->mock(ShipWrapperInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);

        $this->alertedShipsDetection->shouldReceive('getAlertedShipsOnLocation')
            ->with($this->location, $this->user)
            ->once()
            ->andReturn(new ArrayCollection([
                1 => $wrapper1,
                4 => $wrapper2
            ]));

        $this->prestigeCalculation->shouldReceive('getPrestigeOfSpacecraftOrFleet')
            ->with($this->leadShip)
            ->once()
            ->andReturn(111);
        $this->prestigeCalculation->shouldReceive('getPrestigeOfSpacecraftOrFleet')
            ->with($wrapper1)
            ->once()
            ->andReturn(100);
        $this->prestigeCalculation->shouldReceive('getPrestigeOfSpacecraftOrFleet')
            ->with($wrapper2)
            ->once()
            ->andReturn(233);

        $result = $this->subject->isAlertTrap($this->location, $this->leadShip);

        $this->assertFalse($result);
    }
    public function testIsAlertTrapExpectFalseWhenAlertedPrestigeLowEnoughOnRandom(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $this->alertedShipsDetection->shouldReceive('getAlertedShipsOnLocation')
            ->with($this->location, $this->user)
            ->once()
            ->andReturn(new ArrayCollection([1 => $wrapper]));

        $this->prestigeCalculation->shouldReceive('getPrestigeOfSpacecraftOrFleet')
            ->with($this->leadShip)
            ->once()
            ->andReturn(111);
        $this->prestigeCalculation->shouldReceive('getPrestigeOfSpacecraftOrFleet')
            ->with($wrapper)
            ->once()
            ->andReturn(334);

        $this->stuRandom->shouldReceive('rand')
            ->with(0, 334)
            ->once()
            ->andReturn(111);

        $result = $this->subject->isAlertTrap($this->location, $this->leadShip);

        $this->assertFalse($result);
    }
    public function testIsAlertTrapExpectTrueWhenAlertedPrestigeTooHighOnRandom(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $this->alertedShipsDetection->shouldReceive('getAlertedShipsOnLocation')
            ->with($this->location, $this->user)
            ->once()
            ->andReturn(new ArrayCollection([1 => $wrapper]));

        $this->prestigeCalculation->shouldReceive('getPrestigeOfSpacecraftOrFleet')
            ->with($this->leadShip)
            ->once()
            ->andReturn(111);
        $this->prestigeCalculation->shouldReceive('getPrestigeOfSpacecraftOrFleet')
            ->with($wrapper)
            ->once()
            ->andReturn(334);

        $this->stuRandom->shouldReceive('rand')
            ->with(0, 334)
            ->once()
            ->andReturn(112);

        $result = $this->subject->isAlertTrap($this->location, $this->leadShip);

        $this->assertTrue($result);
    }
}
