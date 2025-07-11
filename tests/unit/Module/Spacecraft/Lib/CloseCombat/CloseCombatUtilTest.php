<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class CloseCombatUtilTest extends StuTestCase
{
    private MockInterface&Ship $ship;

    private CloseCombatUtilInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //params
        $this->ship = $this->mock(Ship::class);

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
        $shipCrew1 = $this->mock(CrewAssignment::class);

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
        $shipCrew1 = $this->mock(CrewAssignment::class);
        $shipCrew2 = $this->mock(CrewAssignment::class);
        $shipCrew3 = $this->mock(CrewAssignment::class);
        $shipCrew4 = $this->mock(CrewAssignment::class);
        $shipCrew5 = $this->mock(CrewAssignment::class);
        $shipCrew6 = $this->mock(CrewAssignment::class);
        $crew1 = $this->mock(Crew::class);
        $crew2 = $this->mock(Crew::class);
        $crew3 = $this->mock(Crew::class);
        $crew4 = $this->mock(Crew::class);
        $crew5 = $this->mock(Crew::class);
        $crew6 = $this->mock(Crew::class);

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
            ->andReturn(CrewTypeEnum::NAVIGATION);
        $crew2->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewTypeEnum::SECURITY);
        $crew3->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewTypeEnum::CAPTAIN);
        $crew4->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewTypeEnum::SCIENCE);
        $crew5->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewTypeEnum::SCIENCE);
        $crew6->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(CrewTypeEnum::SCIENCE);

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
        $shipCrew1 = $this->mock(CrewAssignment::class);
        $shipCrew2 = $this->mock(CrewAssignment::class);
        $crew1 = $this->mock(Crew::class);
        $crew2 = $this->mock(Crew::class);
        $faction = $this->mock(Faction::class);

        $shipCrew1->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew1);
        $shipCrew2->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($crew2);
        $crew1->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewTypeEnum::CAPTAIN);
        $crew2->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewTypeEnum::NAVIGATION);

        $faction->shouldReceive('getCloseCombatScore')
            ->withNoArgs()
            ->once()
            ->andReturn(10);

        $result = $this->subject->getCombatValue([$shipCrew1, $shipCrew2], $faction);

        $this->assertEquals(120, $result);
    }
}
