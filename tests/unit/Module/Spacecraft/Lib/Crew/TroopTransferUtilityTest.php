<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\StuTestCase;

class TroopTransferUtilityTest extends StuTestCase
{
    private MockInterface&CrewAssignmentRepositoryInterface $shipCrewRepository;
    private MockInterface&ShipTakeoverManagerInterface $shipTakeoverManager;
    private MockInterface&SpacecraftCrewCalculatorInterface $shipCrewCalculator;

    private TroopTransferUtilityInterface $subject;

    private MockInterface&Ship $ship;

    #[Override]
    protected function setUp(): void
    {
        $this->shipCrewRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->shipTakeoverManager = $this->mock(ShipTakeoverManagerInterface::class);
        $this->shipCrewCalculator = $this->mock(SpacecraftCrewCalculatorInterface::class);

        $this->ship = $this->mock(Ship::class);

        $this->subject = new TroopTransferUtility(
            $this->shipCrewRepository,
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
        $buildplan = $this->mock(SpacecraftBuildplan::class);

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
        $buildplan = $this->mock(SpacecraftBuildplan::class);

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
        $user = $this->mock(User::class);
        $shipCrew1 = $this->mock(CrewAssignment::class);
        $shipCrew2 = $this->mock(CrewAssignment::class);

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
            ->andReturn($this->mock(User::class));

        $result = $this->subject->ownCrewOnTarget($user, $this->ship);

        $this->assertEquals(1, $result);
    }

    public function testForeignerCount(): void
    {
        $user = $this->mock(User::class);
        $shipCrew1 = $this->mock(CrewAssignment::class);
        $shipCrew2 = $this->mock(CrewAssignment::class);

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
            ->andReturn($this->mock(User::class));

        $result = $this->subject->foreignerCount($this->ship);

        $this->assertEquals(1, $result);
    }

    public function testAssignCrewWhenShipTarget(): void
    {
        $shipCrew = $this->mock(CrewAssignment::class);
        $target = $this->mock(Ship::class);
        $takeover = $this->mock(ShipTakeover::class);

        $shipCrew->shouldReceive('clearAssignment')
            ->withNoArgs()
            ->once()
            ->andReturnSelf();
        $shipCrew->shouldReceive('assign')
            ->with($target)
            ->once()
            ->andReturnSelf();
        $shipCrew->shouldReceive('setSlot')
            ->with(null)
            ->once();

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

        $this->shipCrewRepository->shouldReceive('save')
            ->with($shipCrew)
            ->once();

        $this->subject->assignCrew($shipCrew, $target);
    }

    public function testAssignCrewWhenColonyTarget(): void
    {
        $shipCrew = $this->mock(CrewAssignment::class);
        $target = $this->mock(Colony::class);

        $shipCrew->shouldReceive('clearAssignment')
            ->withNoArgs()
            ->once()
            ->andReturnSelf();
        $shipCrew->shouldReceive('assign')
            ->with($target)
            ->once()
            ->andReturnSelf();
        $shipCrew->shouldReceive('setSlot')
            ->with(null)
            ->once();

        $target->shouldReceive('getCrewAssignments->add')
            ->with($shipCrew)
            ->once();

        $this->shipCrewRepository->shouldReceive('save')
            ->with($shipCrew)
            ->once();

        $this->subject->assignCrew($shipCrew, $target);
    }
}
