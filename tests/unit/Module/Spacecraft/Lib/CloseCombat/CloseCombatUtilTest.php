<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Crew\CrewEnum;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class CloseCombatUtilTest extends StuTestCase
{
    private MockInterface&ShipInterface $ship;

    private CloseCombatUtilInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //params
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new CloseCombatUtil();
    }

    public function testGetCombatGroupExpectEmptyWhenNoCrewOnShip(): void
    {
        $crewList = new ArrayCollection();

        $this->ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewList);

        $result = $this->subject->getCombatGroup($this->ship);

        $this->assertTrue($result === []);
    }

    public function testGetCombatGroupWithOnlyOneCrewmann(): void
    {
        $shipCrew1 = $this->mock(CrewAssignmentInterface::class);

        $crewList = new ArrayCollection([$shipCrew1]);

        $this->ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewList);

        $result = $this->subject->getCombatGroup($this->ship);

        $this->assertFalse($result === []);
        $this->assertTrue($result[0] === $shipCrew1);
    }

    public function testGetCombatGroupExpectRightOrder(): void
    {
        $shipCrew1 = $this->mock(CrewAssignmentInterface::class);
        $shipCrew2 = $this->mock(CrewAssignmentInterface::class);
        $shipCrew3 = $this->mock(CrewAssignmentInterface::class);
        $shipCrew4 = $this->mock(CrewAssignmentInterface::class);
        $shipCrew5 = $this->mock(CrewAssignmentInterface::class);
        $shipCrew6 = $this->mock(CrewAssignmentInterface::class);
        $crew1 = $this->mock(CrewInterface::class);
        $crew2 = $this->mock(CrewInterface::class);
        $crew3 = $this->mock(CrewInterface::class);
        $crew4 = $this->mock(CrewInterface::class);
        $crew5 = $this->mock(CrewInterface::class);
        $crew6 = $this->mock(CrewInterface::class);

        $crewList = new ArrayCollection([
            $shipCrew4,
            $shipCrew5,
            $shipCrew1,
            $shipCrew2,
            $shipCrew3,
            $shipCrew6
        ]);

        $shipCrew1->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew1);
        $shipCrew2->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew2);
        $shipCrew3->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew3);
        $shipCrew4->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew4);
        $shipCrew5->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew5);
        $shipCrew6->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew6);

        $crew1->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewEnum::CREW_TYPE_NAVIGATION);
        $crew2->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewEnum::CREW_TYPE_SECURITY);
        $crew3->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewEnum::CREW_TYPE_CAPTAIN);
        $crew4->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewEnum::CREW_TYPE_SCIENCE);
        $crew5->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewEnum::CREW_TYPE_SCIENCE);
        $crew6->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewEnum::CREW_TYPE_SCIENCE);

        $this->ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewList);

        $result = $this->subject->getCombatGroup($this->ship);

        $this->assertTrue(count($result) === 5);
        $this->assertTrue($result[0] === $shipCrew2);
        $this->assertTrue($result[1] === $shipCrew3);
        $this->assertTrue($result[2] === $shipCrew1);
    }

    public function testGetCombatValue(): void
    {
        $shipCrew1 = $this->mock(CrewAssignmentInterface::class);
        $shipCrew2 = $this->mock(CrewAssignmentInterface::class);
        $crew1 = $this->mock(CrewInterface::class);
        $crew2 = $this->mock(CrewInterface::class);
        $faction = $this->mock(FactionInterface::class);

        $shipCrew1->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew1);
        $shipCrew2->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew2);
        $crew1->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewEnum::CREW_TYPE_CAPTAIN);
        $crew2->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewEnum::CREW_TYPE_NAVIGATION);

        $faction->shouldReceive('getCloseCombatScore')
            ->withNoArgs()
            ->once()
            ->andReturn(10);

        $result = $this->subject->getCombatValue([$shipCrew1, $shipCrew2], $faction);

        $this->assertEquals(120, $result);
    }
}
