<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\ActionTestCase;
use Stu\Component\Quest\AwayMission\AwayMissionChoices;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;

final class AwayMissionAssignmentBalanceIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private AwayMissionRepositoryInterface $missions;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->missions = $this->getContainer()->get(AwayMissionRepositoryInterface::class);
    }

    public function testCountsIncludeEverySavedPlayerAssignmentRegardlessOfRewardAndExcludeNpcOtherMissionsAndNullChoices(): void
    {
        $first = $this->mission(42);
        $other = $this->mission(76754);
        $this->progress($first, 101, AwayMissionResearchEnum::SENSORS)->setRewardReceived(true)->setStarted(true);
        $this->progress($first, 102, AwayMissionResearchEnum::SENSORS);
        $this->progress($first, 103, AwayMissionResearchEnum::COMPUTER);
        $this->progress($first, 19, AwayMissionResearchEnum::SENSORS);
        $this->progress($other, 101, AwayMissionResearchEnum::SENSORS);
        $this->progress($other, 102);
        $this->em->flush();

        $counts = $this->missions->getResearchAssignmentCounts($first);

        self::assertSame(2, $counts[AwayMissionResearchEnum::SENSORS->value]);
        self::assertSame(1, $counts[AwayMissionResearchEnum::COMPUTER->value]);
        self::assertCount(2, $counts);
        self::assertSame([AwayMissionResearchEnum::SENSORS->value => 1], $this->missions->getResearchAssignmentCounts($other));
    }

    public function testTheFirstAssignmentUsesAnUnassignedTypeAndSurvivesReloadAndAnotherShip(): void
    {
        $systems = AwayMissionResearchEnum::cases();
        $mission = $this->mission(42);
        $this->progress($mission, 101, $systems[0]);
        $this->progress($mission, 102, $systems[1]);
        $this->em->flush();
        $user = $this->entity(User::class, 103);
        $ship = new Ship();
        $ship->setUser($user);
        $faction = $this->entity(Faction::class, 2);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(0, count($systems) - 3)->andReturn(0);
        $choices = $this->getContainer()->make(AwayMissionChoices::class, ['random' => $random]);

        $this->em->wrapInTransaction(function () use ($mission, $user, $ship, $faction, $choices): void {
            $progress = $this->missions->initializeProgress($mission, $user)->setContactFaction($faction);
            $choices->initialize($progress, $ship);
            $this->missions->saveProgress($progress);
        });
        $this->em->clear();
        $restored = $this->missions->getProgress($mission, $user);
        self::assertNotNull($restored);
        self::assertSame($systems[2], $restored->getResearchSystem());
        $choices->initialize($restored, new Ship());
        self::assertSame($systems[2], $restored->getResearchSystem());
        self::assertNotNull($restored->getContactFaction());
        self::assertSame(2, $restored->getContactFaction()->getId());
        self::assertSame(3, array_sum($this->missions->getResearchAssignmentCounts($mission)));
    }

    public function testTheNewTorpedoTypeWithZeroSavedAssignmentsCatchesUpWithAllOtherEnumCases(): void
    {
        $systems = AwayMissionResearchEnum::cases();
        $mission = $this->mission(42);
        $faction = $this->entity(Faction::class, 1);
        foreach ($systems as $system) {
            if ($system === AwayMissionResearchEnum::TORPEDO_WEAPONS) {
                continue;
            }
            $player = new User()->setUsername('Ausgleich ' . $system->value)->setFaction($faction);
            $this->em->persist($player);
            $this->em->persist(new AwayMissionProgress()->setMission($mission)->setUser($player)->setResearchSystem($system));
        }
        $nextPlayer = new User()->setUsername('Neuer Missionsstarter')->setFaction($faction);
        $this->em->persist($nextPlayer);
        $this->em->flush();
        $before = $this->missions->getResearchAssignmentCounts($mission);
        self::assertCount(count($systems) - 1, $before);
        self::assertSame(0, $before[AwayMissionResearchEnum::TORPEDO_WEAPONS->value] ?? 0);
        $ship = new Ship();
        $ship->setUser($nextPlayer);
        $contact = $this->entity(Faction::class, 2);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(0, 0)->andReturn(0);
        $choices = $this->getContainer()->make(AwayMissionChoices::class, ['random' => $random]);

        $this->em->wrapInTransaction(function () use ($mission, $nextPlayer, $contact, $choices, $ship): void {
            $progress = $this->missions->initializeProgress($mission, $nextPlayer)->setContactFaction($contact);
            $choices->initialize($progress, $ship);
            $this->missions->saveProgress($progress);
        });

        $counts = $this->missions->getResearchAssignmentCounts($mission);
        foreach ($systems as $system) {
            self::assertSame(1, $counts[$system->value]);
        }
        self::assertSame(AwayMissionResearchEnum::TORPEDO_WEAPONS, $this->missions->getProgress($mission, $nextPlayer)?->getResearchSystem());
    }

    private function mission(int $colonyId): AwayMission
    {
        $owner = $this->entity(User::class, 19);
        $quest = new NPCQuest()->setUser($owner)->setUserId(19)->setTitle('Datensatzverteilung')->setStart(time() - 100);
        $mission = new AwayMission()->setColony($this->entity(Colony::class, $colonyId))->setQuest($quest)->setTitle('Datensatzverteilung');
        $this->em->persist($quest);
        $this->em->persist($mission);
        return $mission;
    }

    private function progress(AwayMission $mission, int $userId, ?AwayMissionResearchEnum $system = null): AwayMissionProgress
    {
        $progress = new AwayMissionProgress()->setMission($mission)->setUser($this->entity(User::class, $userId));
        if ($system !== null) {
            $progress->setResearchSystem($system);
        }
        $this->em->persist($progress);
        return $progress;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function entity(string $class, int $id): object
    {
        $entity = $this->em->find($class, $id);
        self::assertNotNull($entity);
        return $entity;
    }
}
