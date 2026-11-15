<?php

declare(strict_types=1);

namespace Stu\Component\Quest\AwayMission;

use Mockery\MockInterface;
use Override;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\StuTestCase;

final class AwayMissionChoicesTest extends StuTestCase
{
    private StuRandom&MockInterface $random;
    private FactionRepositoryInterface&MockInterface $factions;
    private AwayMissionRepositoryInterface&MockInterface $missions;
    private AwayMissionChoices $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->random = $this->mock(StuRandom::class);
        $this->factions = $this->mock(FactionRepositoryInterface::class);
        $this->missions = $this->mock(AwayMissionRepositoryInterface::class);
        $this->subject = new AwayMissionChoices($this->random, $this->factions, $this->missions);
    }

    public function testAnUnassignedTypeCatchesUpBeforeAnyAlreadyAssignedTypeIsChosenAgain(): void
    {
        $systems = AwayMissionResearchEnum::cases();
        $unassigned = array_pop($systems);
        $counts = array_fill_keys(array_map(static fn (AwayMissionResearchEnum $system): string => $system->value, $systems), 5);
        $progress = $this->progress();
        $this->expectCountsAfterMissionLock($progress->getMission(), $counts);
        $this->factions->shouldReceive('getByChooseable')->once()->with(true)->andReturn([$progress->getContactFaction()]);
        $this->random->shouldReceive('rand')->once()->with(0, 0)->andReturn(0);

        $this->subject->initialize($progress, $this->ship());

        self::assertSame($unassigned, $progress->getResearchSystem());
    }

    public function testOnlyTheLeastAssignedTypesParticipateInTheRandomTie(): void
    {
        $systems = AwayMissionResearchEnum::cases();
        $last = $systems[count($systems) - 1];
        $counts = array_fill_keys(array_map(static fn (AwayMissionResearchEnum $system): string => $system->value, $systems), 7);
        $counts[$systems[1]->value] = 2;
        $counts[$last->value] = 2;
        $progress = $this->progress();
        $this->expectCountsAfterMissionLock($progress->getMission(), $counts);
        $this->factions->shouldReceive('getByChooseable')->once()->with(true)->andReturn([$progress->getContactFaction()]);
        $this->random->shouldReceive('rand')->once()->with(0, 1)->andReturn(1);

        $this->subject->initialize($progress, $this->ship());

        self::assertSame($last, $progress->getResearchSystem());
    }

    public function testAllEnumCasesParticipateWhenTheMissionHasNoAssignments(): void
    {
        $systems = AwayMissionResearchEnum::cases();
        $lastIndex = count($systems) - 1;
        $progress = $this->progress();
        $this->expectCountsAfterMissionLock($progress->getMission(), []);
        $this->factions->shouldReceive('getByChooseable')->once()->with(true)->andReturn([$progress->getContactFaction()]);
        $this->random->shouldReceive('rand')->once()->with(0, $lastIndex)->andReturn($lastIndex);

        $this->subject->initialize($progress, $this->ship());

        self::assertSame($systems[$lastIndex], $progress->getResearchSystem());
    }

    public function testReopeningTheMissionOnAnotherShipNeverRerollsTheSystemOrContact(): void
    {
        $progress = $this->progress()->setResearchSystem(AwayMissionResearchEnum::SENSORS)->setReportingCrewId(123);
        $contact = $progress->getContactFaction();
        $this->missions->shouldNotReceive('lockMission', 'getResearchAssignmentCounts');
        $this->factions->shouldNotReceive('getByChooseable');
        $this->random->shouldNotReceive('rand');

        $this->subject->initialize($progress, $this->ship());
        $this->subject->initialize($progress, new Ship());

        self::assertSame(AwayMissionResearchEnum::SENSORS, $progress->getResearchSystem());
        self::assertSame($contact, $progress->getContactFaction());
        self::assertSame(123, $progress->getReportingCrewId());
    }

    public function testAnExistingSystemIsPreservedEvenWhenTheContactStillNeedsToBeChosen(): void
    {
        $progress = new AwayMissionProgress()->setResearchSystem(AwayMissionResearchEnum::ENERGY_WEAPONS);
        $own = $this->faction(1);
        $foreign = $this->faction(2);
        $this->missions->shouldNotReceive('lockMission', 'getResearchAssignmentCounts');
        $this->factions->shouldReceive('getByChooseable')->once()->with(true)->andReturn([$own, $foreign]);
        $this->random->shouldReceive('rand')->once()->with(0, 0)->andReturn(0);

        $this->subject->initialize($progress, $this->ship());

        self::assertSame(AwayMissionResearchEnum::ENERGY_WEAPONS, $progress->getResearchSystem());
        self::assertSame($foreign, $progress->getContactFaction());
    }

    /** @param array<string, int> $counts */
    private function expectCountsAfterMissionLock(AwayMission $mission, array $counts): void
    {
        $locked = false;
        $this->missions->shouldReceive('lockMission')->once()->with($mission)->andReturnUsing(static function () use (&$locked): void {
            $locked = true;
        });
        $this->missions->shouldReceive('getResearchAssignmentCounts')->once()->with($mission)->andReturnUsing(static function () use (&$locked, $counts): array {
            self::assertTrue($locked);
            return $counts;
        });
    }

    private function progress(): AwayMissionProgress
    {
        return new AwayMissionProgress()->setMission(new AwayMission())->setContactFaction($this->faction(2));
    }

    private function ship(): Ship
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getFactionId')->andReturn(1);
        $ship = new Ship();
        $ship->setUser($user);
        return $ship;
    }

    private function faction(int $id): Faction&MockInterface
    {
        $faction = $this->mock(Faction::class);
        $faction->shouldReceive('getId')->andReturn($id);
        return $faction;
    }
}
