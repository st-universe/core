<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Crew;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipTakeoverRepositoryInterface;
use Stu\StuTestCase;

class TroopTransferUtilityTest extends StuTestCase
{
    /** @var MockInterface&ShipTakeoverManagerInterface */
    private MockInterface $shipTakeoverManager;

    /** @var MockInterface&ShipCrewCalculatorInterface */
    private MockInterface $shipCrewCalculator;

    private TroopTransferUtilityInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    protected function setUp(): void
    {
        $this->shipTakeoverManager = $this->mock(ShipTakeoverManagerInterface::class);
        $this->shipCrewCalculator = $this->mock(ShipCrewCalculatorInterface::class);

        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new TroopTransferUtility(
            $this->shipTakeoverManager,
            $this->shipCrewCalculator
        );
    }

    public function testGetFreeQuartersExpectZeroWhenTooFull(): void
    {
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->shipCrewCalculator->shouldReceive('getMaxCrewCountByShip')
            ->with($this->ship)
            ->once()
            ->andReturn(41);

        $result = $this->subject->getFreeQuarters($this->ship);

        $this->assertEquals(0, $result);
    }

    public function testGetFreeQuartersExpectFreePlace(): void
    {
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->shipCrewCalculator->shouldReceive('getMaxCrewCountByShip')
            ->with($this->ship)
            ->once()
            ->andReturn(43);

        $result = $this->subject->getFreeQuarters($this->ship);

        $this->assertEquals(1, $result);
    }

    public function testGetBeamableTroopCountExpectZeroWhenNoBuildplan(): void
    {
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->getBeamableTroopCount($this->ship);

        $this->assertEquals(0, $result);
    }

    public function testGetBeamableTroopCountExpectZeroWhenNotEnoughCrew(): void
    {
        $buildplan = $this->mock(ShipBuildplanInterface::class);

        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(40);

        $buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getBeamableTroopCount($this->ship);

        $this->assertEquals(0, $result);
    }

    public function testGetBeamableTroopCountExpectFreeCrewCount(): void
    {
        $buildplan = $this->mock(ShipBuildplanInterface::class);

        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(43);

        $buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getBeamableTroopCount($this->ship);

        $this->assertEquals(1, $result);
    }

    public function testGetOwnCrewOnTarget(): void
    {
        $user = $this->mock(UserInterface::class);
        $shipCrew1 = $this->mock(ShipCrewInterface::class);
        $shipCrew2 = $this->mock(ShipCrewInterface::class);

        $crewAssignments = new ArrayCollection([$shipCrew1, $shipCrew2]);

        $this->ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewAssignments);

        $shipCrew1->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $shipCrew2->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $result = $this->subject->ownCrewOnTarget($user, $this->ship);

        $this->assertEquals(1, $result);
    }

    public function testForeignerCount(): void
    {
        $user = $this->mock(UserInterface::class);
        $shipCrew1 = $this->mock(ShipCrewInterface::class);
        $shipCrew2 = $this->mock(ShipCrewInterface::class);

        $crewAssignments = new ArrayCollection([$shipCrew1, $shipCrew2]);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewAssignments);

        $shipCrew1->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $shipCrew2->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $result = $this->subject->foreignerCount($this->ship);

        $this->assertEquals(1, $result);
    }

    public function testAssignCrewWhenShipTarget(): void
    {
        $shipCrew = $this->mock(ShipCrewInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $takeover = $this->mock(ShipTakeoverInterface::class);

        $crewAssignments = new ArrayCollection([$shipCrew]);

        $shipCrew->shouldReceive('getShip')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $shipCrew->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $shipCrew->shouldReceive('setShip')
            ->with($target)
            ->once()
            ->andReturnSelf();
        $shipCrew->shouldReceive('setColony')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $shipCrew->shouldReceive('setSlot')
            ->with(null)
            ->once();

        $ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewAssignments);

        $target->shouldReceive('getTakeoverPassive')
            ->withNoArgs()
            ->once()
            ->andReturn($takeover);
        $target->shouldReceive('getCrewAssignments->add')
            ->with($shipCrew)
            ->once();

        $this->shipTakeoverManager->shouldReceive('cancelTakeover')
            ->with(
                $takeover,
                ', da das Schiff bemannt wurde'
            )
            ->once();

        $this->subject->assignCrew($shipCrew, $target);

        $this->assertTrue($crewAssignments->isEmpty());
    }

    public function testAssignCrewWhenColonyTarget(): void
    {
        $shipCrew = $this->mock(ShipCrewInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $target = $this->mock(ColonyInterface::class);

        $crewAssignments = new ArrayCollection([$shipCrew]);

        $shipCrew->shouldReceive('getShip')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $shipCrew->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
        $shipCrew->shouldReceive('setShip')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $shipCrew->shouldReceive('setColony')
            ->with($target)
            ->once()
            ->andReturnSelf();
        $shipCrew->shouldReceive('setSlot')
            ->with(null)
            ->once();

        $colony->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewAssignments);

        $target->shouldReceive('getCrewAssignments->add')
            ->with($shipCrew)
            ->once();

        $this->subject->assignCrew($shipCrew, $target);

        $this->assertTrue($crewAssignments->isEmpty());
    }
}
