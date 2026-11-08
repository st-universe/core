<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\StuTestCase;

final class BorgSphereRaidTeamTest extends StuTestCase
{
    private CrewRepositoryInterface&MockInterface $crews;
    private CrewAssignmentRepositoryInterface&MockInterface $assignments;
    private SpacecraftShutdownInterface&MockInterface $shutdown;
    private StuRandom&MockInterface $random;
    private BorgSphereRaidTeam $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->crews = $this->mock(CrewRepositoryInterface::class);
        $this->assignments = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->shutdown = $this->mock(SpacecraftShutdownInterface::class);
        $this->random = $this->mock(StuRandom::class);
        $this->subject = new BorgSphereRaidTeam($this->crews, $this->assignments, $this->shutdown, $this->mock(SpacecraftMovementPublisherInterface::class), $this->random);
    }

    /** @return iterable<string, array{int, int}> */
    public static function excessCrew(): iterable
    {
        yield 'minimum crew only' => [10, 0];
        yield 'minimum plus one' => [11, 1];
        yield 'minimum plus two' => [12, 2];
        yield 'minimum plus five' => [15, 5];
        yield 'more than five surplus' => [18, 5];
    }

    #[DataProvider('excessCrew')]
    public function testOnlyCrewAboveTheBuildplanMinimumCanLeave(int $crew, int $expected): void
    {
        $ship = $this->ship($crew);
        $team = $this->subject->select($ship, 5);
        self::assertCount($expected, $team);
        foreach ($team as $assignment) {
            self::assertSame(CrewTypeEnum::CREWMAN, $assignment->getSlot());
        }
        self::assertGreaterThanOrEqual(10, $ship->getCrewCount() - count($team));
    }

    public function testCrewmenAreSelectedBeforeUnassignedCrewAndOfficersStayAboard(): void
    {
        $ship = $this->ship(15);
        $unassigned = $ship->getCrewAssignments()->get(6)->setSlot(null);
        $team = $this->subject->select($ship, 5);
        self::assertNotContains($unassigned, $team);
        self::assertSame(CrewTypeEnum::CREWMAN, $team[0]->getSlot());
        self::assertNotContains($ship->getCrewAssignments()->get(0), $team);
        self::assertNotContains($ship->getCrewAssignments()->get(1), $team);
    }

    public function testLossesAreCappedAtTheCurrentSurplusAndNeverUseOfficers(): void
    {
        $ship = $this->ship(12);
        $team = $this->subject->select($ship, 5);
        $team[] = $ship->getCrewAssignments()->get(0);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('get')->once()->andReturn($ship);
        $this->random->shouldReceive('array_rand')->twice()->andReturnUsing(static fn (array $crew): int => array_key_first($crew));
        $this->crews->shouldReceive('delete')->twice()->with(Mockery::type(Crew::class));
        $this->assignments->shouldReceive('delete')->twice()->with(Mockery::type(CrewAssignment::class));
        $this->shutdown->shouldNotReceive('shutdown');
        $lost = $this->subject->assimilate($team, 5, $wrapper);
        self::assertCount(2, $lost);
        self::assertSame(10, $ship->getCrewCount());
        self::assertSame(CrewTypeEnum::CAPTAIN, $ship->getCrewAssignments()->get(0)->getSlot());
    }

    private function ship(int $crew): Ship
    {
        $ship = new Ship();
        $ship->setBuildplan(new SpacecraftBuildplan()->setCrew(10));
        for ($i = 0; $i < $crew; $i++) {
            $assignment = new CrewAssignment()->setSpacecraft($ship)->setCrew(new Crew()->setName('Crew ' . $i))->setSlot($i < 6 ? CrewTypeEnum::from($i + 1) : CrewTypeEnum::CREWMAN);
            $ship->getCrewAssignments()->add($assignment);
        }
        return $ship;
    }
}
