<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgAssimilationCapture;
use Stu\Component\Borg\BorgAssimilationLifecycle;
use Stu\Component\Borg\BorgAssimilationRelease;
use Stu\Component\Borg\BorgAssimilationService;
use Stu\Component\Borg\BorgCompanionDeparture;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgTractorRescue;
use Stu\Component\Borg\BorgTractorRescueResponse;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManager;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TractorBeamShipSystem;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Tick\Process\BorgAssimilationTick;
use Stu\Module\Tick\Process\BorgSphereTick;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\History;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\RumpAssimilateable;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;

final class BorgCubeAssimilationIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private BorgAssimilationRepositoryInterface $repository;
    private BorgAssimilationService $service;
    private BorgSphereEvent $event;
    private Ship $cube;
    private Ship $target;
    private User $owner;
    private int $now;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(BorgAssimilationRepositoryInterface::class);
        $this->now = $container->get(StuTime::class)->time();
        $this->target = $this->em->find(Ship::class, 42);
        $this->target->getRump()->setCategoryId(SpacecraftRumpCategoryEnum::CRUISER);
        $this->cube = $this->em->find(Ship::class, 77);
        $container->get(LeaveFleetInterface::class)->leaveFleet($this->cube);
        $this->owner = $this->target->getUser();
        $colony = $this->em->find(Colony::class, 42);
        $this->target->setLocation($colony->getStarsystemMap())->setName('Assimilationstest');
        $this->cube->setLocation($colony->getStarsystemMap())->setUser($this->em->find(User::class, 10))->setName('Testkubus')->setFleet(null);
        $this->target->getCondition()->setDisabled(false)->setIsDestroyed(false);
        $this->cube->getCondition()->setDisabled(false)->setIsDestroyed(false);
        foreach ([$this->target, $this->cube] as $ship) {
            $this->system($ship, SpacecraftSystemTypeEnum::SHIELDS)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            $this->system($ship, SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            if ($ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)) {
                $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
        }
        $this->system($this->cube, SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        $location = $this->cube->getLocation();
        $location->getSpacecrafts()->clear();
        $location->getSpacecrafts()->set($this->target->getId(), $this->target);
        $location->getSpacecrafts()->set($this->cube->getId(), $this->cube);
        $quest = new NPCQuest()->setUser($this->cube->getUser())->setUserId(10)->setTitle('Kubustest')->setStart($this->now - 100);
        $configuration = new BorgSphereConfiguration()->setId(1)->setColony($colony)->setQuest($quest)->setUser($this->cube->getUser())
            ->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))->setEndTime($this->now + 86400)
            ->setSettings(['assimilation_enabled' => 1, 'assimilation_duration_seconds' => 900, 'nanite_commodity_id' => 2, 'assimilation_nanite_min' => 1, 'assimilation_nanite_max' => 30]);
        $this->event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId($this->cube->getId())->setPhase(BorgSpherePhaseEnum::ROAMING)->setNextTickAt($this->now + 3600);
        $this->em->persist($quest);
        $this->em->persist($configuration);
        $this->em->persist($this->event);
        $this->em->persist(new RumpAssimilateable()->setRump($this->target->getRump()));
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturnUsing(fn (): int => $this->now);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('array_rand')->andReturn(0);
        $random->shouldReceive('rand')->with(1, 100)->andReturn(1);
        $random->shouldReceive('rand')->with(1, 30)->andReturn(30);
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $capture = $container->make(BorgAssimilationCapture::class, ['time' => $clock, 'random' => $random, 'relations' => $relations]);
        $release = $container->make(BorgAssimilationRelease::class, ['random' => $random]);
        $this->service = $container->make(BorgAssimilationService::class, ['capture' => $capture, 'release' => $release, 'time' => $clock]);
        $this->em->flush();
    }

    public function testCaptureIgnoresShieldsAndRemovesTheVictimFromItsFleet(): void
    {
        $fleet = $this->em->find(Fleet::class, 42);
        $fleet->setUser($this->owner)->setLeadShip($this->target);
        $this->target->setFleet($fleet)->setIsFleetLeader(true);
        $other = $this->em->find(Ship::class, 78);
        $other->setUser($this->owner)->setFleet($fleet)->setIsFleetLeader(false);
        $this->em->flush();

        $this->capture();

        self::assertTrue($this->target->isBeingAssimilated());
        self::assertSame($this->owner, $this->target->getUser());
        self::assertTrue($this->target->isShielded());
        self::assertTrue($this->cube->isShielded());
        self::assertNull($this->target->getFleet());
        self::assertSame($other, $fleet->getLeadShip());
        self::assertSame($this->target, $this->cube->getTractoredShip());
        self::assertSame($this->cube, $this->target->getTractoringSpacecraft());
        self::assertSame(0, $this->repository->getBySpacecraft($this->target)->getProgress());
    }

    public function testMinuteTickCompletesAfterFifteenMinutesAndTransfersCrewRepairsAndFleet(): void
    {
        $container = $this->getContainer();
        $crewCreator = $container->get(CrewCreatorInterface::class);
        for ($index = 0; $index < 2; $index++) {
            $assignment = $crewCreator->create($this->owner->getId())->setSpacecraft($this->target)->setSlot(CrewTypeEnum::CREWMAN);
            $this->target->getCrewAssignments()->add($assignment);
            $this->em->persist($assignment);
        }
        $this->target->getBuildplan()->setCrew(1000);
        $this->target->setMaxHull(1000)->setMaxShield(2000);
        $this->target->getCondition()->setHull(100)->setShield(200);
        $this->system($this->target, SpacecraftSystemTypeEnum::IMPULSEDRIVE)->setStatus(25);
        $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->target);
        $wrapper->getEpsSystemData()->setEps(0)->setBattery(0)->update();
        $wrapper->getWarpDriveSystemData()->setWarpDrive(0)->update();
        $this->em->flush();
        $startedAt = $this->now;
        $this->capture();
        $tick = new BorgAssimilationTick($this->service);
        foreach ([59 => 0, 60 => 666, 120 => 1333, 899 => 9333] as $seconds => $expected) {
            $this->now = $startedAt + $seconds;
            $this->em->wrapInTransaction(fn () => $tick->work());
            self::assertSame($expected, $this->repository->getBySpacecraft($this->target)->getProgress());
            self::assertSame($this->owner, $this->target->getUser());
        }
        $this->now = $startedAt + 900;

        $this->em->wrapInTransaction(fn () => $tick->work());

        $entry = $this->repository->getBySpacecraft($this->target);
        self::assertTrue($entry->isComplete());
        self::assertFalse($this->target->isBeingAssimilated());
        self::assertSame($this->cube->getUser(), $this->target->getUser());
        self::assertTrue($this->target->hasEnoughCrew());
        self::assertLessThan(1000, $this->target->getCrewCount());
        foreach ($this->target->getCrewAssignments() as $assignment) {
            self::assertSame($this->cube->getUser(), $assignment->getUser());
            self::assertSame($this->cube->getUser(), $assignment->getCrew()->getUser());
        }
        self::assertNotNull($this->cube->getFleet());
        self::assertSame($this->cube, $this->target->getFleet()->getLeadShip());
        self::assertSame($this->cube->getFleet(), $this->target->getFleet());
        self::assertSame(1000, $this->target->getCondition()->getHull());
        self::assertSame(2000, $this->target->getCondition()->getShield());
        foreach ($this->target->getSystems() as $system) {
            self::assertSame(100, $system->getStatus());
        }
        $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->target);
        self::assertSame($wrapper->getEpsSystemData()->getMaxEps(), $wrapper->getEpsSystemData()->getEps());
        self::assertSame($wrapper->getEpsSystemData()->getMaxBattery(), $wrapper->getEpsSystemData()->getBattery());
        self::assertSame($wrapper->getWarpDriveSystemData()->getMaxWarpdrive(), $wrapper->getWarpDriveSystemData()->getWarpDrive());
        self::assertNull($this->cube->getTractoredShip());
        $history = array_values(array_filter($this->em->getRepository(History::class)->findAll(), static fn (History $entry): bool => str_contains($entry->getText(), ' wurde in Sektor ') && str_contains($entry->getText(), ' assimiliert')));
        self::assertCount(1, $history);
        self::assertStringContainsString('Assimilationstest', $history[0]->getText());
        self::assertStringContainsString($this->owner->getName(), $history[0]->getText());
        self::assertStringContainsString('Testkubus', $history[0]->getText());
        self::assertSame($this->cube->getLocation(), $history[0]->getLocation());
        $this->em->wrapInTransaction(fn () => $tick->work());
        self::assertCount(1, array_filter($this->em->getRepository(History::class)->findAll(), static fn (History $entry): bool => str_contains($entry->getText(), ' assimiliert')));
    }

    public function testReleaseFreezesScarringAndRoundsNanitesUpExactlyOnce(): void
    {
        $before = $this->amount();
        $startedAt = $this->now;
        $this->capture();
        self::assertSame($before, $this->amount());
        $this->now = $startedAt + 60;

        $this->em->wrapInTransaction(fn () => $this->service->releaseForSource($this->cube));

        $entry = $this->repository->getBySpacecraft($this->target);
        self::assertSame(666, $entry->getProgress());
        self::assertFalse($entry->isActive());
        self::assertSame($before + 2, $this->amount());
        self::assertSame($this->owner, $this->target->getUser());
        self::assertNull($this->target->getTractoringSpacecraft());
        self::assertNull($this->cube->getTractoredShip());
        $this->now += 3600;
        $this->em->wrapInTransaction(function (): void {
            $this->service->releaseForSource($this->cube);
            $this->service->process();
        });
        self::assertSame(666, $entry->getProgress());
        self::assertSame($before + 2, $this->amount());
    }

    public function testTractorDeactivationImmediatelyReleasesTheVictim(): void
    {
        $this->capture();
        $this->now += 420;
        $container = $this->getContainer();
        $tractor = $container->make(TractorBeamShipSystem::class, ['assimilation' => new BorgAssimilationLifecycle(fn (): BorgAssimilationService => $this->service)]);
        $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->cube);

        $this->em->wrapInTransaction(fn () => $tractor->deactivate($wrapper));

        self::assertFalse($this->target->isBeingAssimilated());
        self::assertSame(4666, $this->repository->getBySpacecraft($this->target)->getProgress());
        self::assertNull($this->cube->getTractoredShip());
        self::assertFalse($this->cube->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM));
    }

    /** @return iterable<string, array{string}> */
    public static function interruptions(): iterable
    {
        yield 'cube disabled' => ['disabled'];
        yield 'tractor damaged' => ['tractor'];
        yield 'cube destroyed' => ['destroyed'];
    }

    #[DataProvider('interruptions')]
    public function testMinuteTickReleasesVictimsIfTheCubeCannotContinue(string $interruption): void
    {
        $this->capture();
        $this->now += 300;
        if ($interruption === 'disabled') {
            $this->cube->getCondition()->setDisabled(true);
        } elseif ($interruption === 'tractor') {
            $this->cube->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(0);
        } else {
            $this->cube->getCondition()->setIsDestroyed(true);
        }

        $this->em->wrapInTransaction(fn () => new BorgAssimilationTick($this->service)->work());

        self::assertFalse($this->target->isBeingAssimilated());
        self::assertSame($this->owner, $this->target->getUser());
        self::assertSame(3333, $this->repository->getBySpacecraft($this->target)->getProgress());
        self::assertNull($this->cube->getTractoredShip());
    }

    public function testDestroyingACapturedShipRemovesItsAssimilationRecord(): void
    {
        $this->capture();
        $id = $this->target->getId();
        $this->target->getCondition()->setIsDestroyed(true);
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->target);
        $destruction = $this->getContainer()->get(SpacecraftDestructionInterface::class);

        $this->em->wrapInTransaction(fn () => $destruction->destroy($this->cube, $wrapper, SpacecraftDestructionCauseEnum::SHIP_FIGHT, new InformationWrapper()));

        self::assertNull($this->em->find(SpacecraftAssimilated::class, $id));
        self::assertNull($this->em->find(Ship::class, $id));
        self::assertNull($this->cube->getTractoredShip());
    }

    public function testRemovingTheCubeClearsSourceReferencesOfCompletedCompanions(): void
    {
        $this->capture();
        $this->now += 900;
        $this->em->wrapInTransaction(fn () => $this->service->process());
        $entry = $this->repository->getBySpacecraft($this->target);
        self::assertTrue($entry->isComplete());
        $remover = $this->getContainer()->get(SpacecraftRemoverInterface::class);

        $this->em->wrapInTransaction(fn () => $remover->remove($this->cube, true));

        self::assertNull($entry->getSource());
        self::assertTrue($entry->isComplete());
        self::assertSame(10000, $entry->getProgress());
    }

    public function testEventEscapeTakesLocalCompanionsAndReleasesPendingShipsButLeavesRemoteCompanions(): void
    {
        $container = $this->getContainer();
        $assignment = $container->get(CrewCreatorInterface::class)->create($this->owner->getId())->setSpacecraft($this->target)->setSlot(CrewTypeEnum::CREWMAN);
        $this->target->getCrewAssignments()->add($assignment);
        $this->em->persist($assignment);
        $this->em->flush();
        $crewId = $assignment->getCrew()->getId();
        $companionId = $this->target->getId();
        $cubeId = $this->cube->getId();
        $this->capture();
        $this->now += 900;
        $this->em->wrapInTransaction(fn () => $this->service->process());
        $fleet = $this->cube->getFleet();
        self::assertNotNull($fleet);
        $remote = $this->em->find(Ship::class, 79);
        $remote->setUser($this->cube->getUser())->setFleet($fleet)->setIsFleetLeader(false)
            ->setLocation($this->event->getConfiguration()->getColony()->getSystem()->getMap());
        $remoteEntry = new SpacecraftAssimilated()->setSpacecraft($remote)->start($this->cube, $this->now - 900, 900)->complete($this->now);
        $this->repository->save($remoteEntry);
        $pending = $this->em->find(Ship::class, 78);
        $pending->setUser($this->owner)->setLocation($this->cube->getLocation())->setFleet(null)->setDockedTo(null);
        $pending->getCondition()->setDisabled(false)->setIsDestroyed(false);
        $this->system($pending, SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        if ($pending->hasSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)) {
            $pending->getSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        if (!$this->repository->isAssimilateable($pending->getRump())) {
            $this->em->persist(new RumpAssimilateable()->setRump($pending->getRump()));
        }
        $this->cube->getLocation()->getSpacecrafts()->set($pending->getId(), $pending);
        $this->em->flush();
        self::assertTrue($this->em->wrapInTransaction(fn (): bool => $this->service->tryCapture($this->event, $this->cube)));
        self::assertTrue($pending->isBeingAssimilated());
        $this->now += 300;
        $this->expireEvent();

        self::assertSame(BorgSpherePhaseEnum::ESCAPED, $this->event->getPhase());
        self::assertNull($this->event->getShipId());
        self::assertNull($this->em->find(Ship::class, $cubeId));
        self::assertNull($this->em->find(Ship::class, $companionId));
        self::assertNull($this->em->find(SpacecraftAssimilated::class, $companionId));
        self::assertNull($this->em->find(Crew::class, $crewId));
        self::assertSame($pending, $this->em->find(Ship::class, $pending->getId()));
        self::assertSame($this->owner, $pending->getUser());
        self::assertFalse($pending->isBeingAssimilated());
        self::assertSame(3333, $pending->getAssimilation()->getProgress());
        self::assertSame($remote, $this->em->find(Ship::class, $remote->getId()));
        self::assertTrue($remoteEntry->isComplete());
        self::assertNull($remoteEntry->getSource());
        self::assertSame($remote, $remote->getFleet()->getLeadShip());
    }

    public function testEventEscapeCompletesAnElapsedAssimilationBeforeTakingTheNewCompanion(): void
    {
        $cubeId = $this->cube->getId();
        $targetId = $this->target->getId();
        $this->capture();
        $this->now += 900;

        $this->expireEvent();

        self::assertSame(BorgSpherePhaseEnum::ESCAPED, $this->event->getPhase());
        self::assertNull($this->em->find(Ship::class, $cubeId));
        self::assertNull($this->em->find(Ship::class, $targetId));
        self::assertNull($this->em->find(SpacecraftAssimilated::class, $targetId));
        self::assertNotEmpty(array_filter($this->em->getRepository(History::class)->findAll(), static fn (History $entry): bool => str_contains($entry->getText(), 'Assimilationstest') && str_contains($entry->getText(), ' assimiliert')));
    }

    /** @return iterable<string, array{list<int>, bool, int, int, bool}> */
    public static function rescueShots(): iterable
    {
        yield 'all three salvos miss at six percent roll' => [[6, 6, 6], false, 3, 0, false];
        yield 'second salvo succeeds at five percent boundary' => [[6, 5], false, 2, 0, true];
        yield 'fleet fires even when selected ship is not leader' => [[6, 6, 6, 6, 6, 6], true, 3, 3, false];
        yield 'fleet stops at first successful rescue salvo' => [[6, 6, 6, 6, 5], true, 3, 2, true];
        yield 'leader never fires after source rescues the ship' => [[5], true, 1, 0, true];
    }

    /** @param list<int> $rolls */
    #[DataProvider('rescueShots')]
    public function testRescueModulatesEachEnergySalvoAndTheCubeReturnsFireOnce(array $rolls, bool $withFleet, int $sourceShots, int $fleetShots, bool $rescued): void
    {
        $container = $this->getContainer();
        $configuration = $this->event->getConfiguration();
        $configuration->setSettings([...$configuration->getSettings(), 'tractor_rescue_chance_percent' => 5]);
        $rescuer = $this->em->find(Ship::class, 78);
        $rescuer->setUser($this->owner)->setLocation($this->cube->getLocation())->setFleet(null)->setDockedTo(null);
        $fleetMember = $this->em->find(Ship::class, 79);
        $fleetMember->setUser($this->owner)->setLocation($this->cube->getLocation())->setFleet(null)->setDockedTo(null);
        if ($withFleet) {
            $fleet = new Fleet()->setUser($this->owner)->setName('Rettungsflotte')->setLeadShip($fleetMember);
            $this->em->persist($fleet);
            $rescuer->setFleet($fleet)->setIsFleetLeader(false);
            $fleetMember->setFleet($fleet)->setIsFleetLeader(true);
        }
        $rescuer->getBuildplan()->setCrew(0);
        $this->cube->getBuildplan()->setCrew(0);
        foreach ([$rescuer, $fleetMember, $this->cube] as $ship) {
            $this->system($ship, SpacecraftSystemTypeEnum::NBS)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            $this->system($ship, SpacecraftSystemTypeEnum::PHASER)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            $this->system($ship, SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            if ($ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)) {
                $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
            $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($ship)->getEpsSystemData()->setEps(100)->update();
        }
        $rescuer->getRump()->setPhaserVolleys(3);
        $this->em->flush();
        $this->capture();
        $this->now += 420;
        $before = $this->amount();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->with(1, 100)->times(count($rolls))->andReturn(...$rolls);
        $counterfire = $this->mock(SpacecraftAttackCycleInterface::class);
        $counterfire->shouldReceive('cycle')->once()->andReturnUsing(function (BattlePartyInterface $attacker, BattlePartyInterface $defender, SpacecraftAttackCauseEnum $cause) use ($container, $rescuer, $withFleet): MessageCollectionInterface {
            self::assertSame($this->cube, $attacker->getLeader()->get());
            self::assertSame($rescuer, $defender->getLeader()->get());
            self::assertSame($withFleet ? 2 : 1, $defender->getActiveMembers()->count());
            self::assertSame(SpacecraftAttackCauseEnum::BORG_TRACTOR_RESCUE_RESPONSE, $cause);
            self::assertTrue($cause->isOneWay());
            $messages = $container->get(MessageFactoryInterface::class)->createMessageCollection();
            $messages->addMessageBy('Der Testkubus feuert zurück', $this->owner->getId());
            return $messages;
        });
        $response = $container->make(BorgTractorRescueResponse::class, ['cycle' => $counterfire]);
        $lifecycle = new BorgAssimilationLifecycle(fn (): BorgAssimilationService => $this->service);
        $tractor = $container->make(TractorBeamShipSystem::class, ['assimilation' => $lifecycle]);
        $systems = new SpacecraftSystemManager([SpacecraftSystemTypeEnum::TRACTOR_BEAM->value => $tractor], $container->get(StuTime::class));
        $rescue = $container->make(BorgTractorRescue::class, ['random' => $random, 'assimilation' => $lifecycle, 'systems' => $systems, 'response' => $response]);
        $source = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($rescuer);
        $target = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->cube);
        $report = new InformationWrapper();

        self::assertTrue($target->canFire());
        self::assertTrue($this->cube->hasEnoughCrew());
        self::assertFalse($rescuer->getUser()->isVacationRequestOldEnough());
        self::assertTrue($container->get(FightLibInterface::class)->canAttackTarget($this->cube, $rescuer, true));

        self::assertTrue($this->em->wrapInTransaction(fn (): bool => $rescue->attack($source, $target, $report)), implode(' ', $report->getInformations()));

        self::assertSame(100 - $sourceShots, $source->getEpsSystemData()->getEps());
        self::assertSame(100 - $fleetShots, $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($fleetMember)->getEpsSystemData()->getEps());
        self::assertSame($sourceShots + $fleetShots, substr_count($report->getInformationsAsString(), 'beschießt den Traktoremitter'));
        self::assertContains('Der Testkubus feuert zurück', $report->getInformations());
        self::assertSame(!$rescued, $this->target->isBeingAssimilated());
        self::assertSame($before + ($rescued ? 14 : 0), $this->amount());
        self::assertSame(!$rescued, $this->cube->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM));
        self::assertSame($rescued ? 75 : 100, $this->cube->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getStatus());
        self::assertTrue($this->cube->isShielded());
    }

    public function testActualCounterfireAppearsInTheReportWithoutAnotherPlayerAttackRound(): void
    {
        $container = $this->getContainer();
        $wrappers = $container->get(SpacecraftWrapperFactoryInterface::class);
        $rescuer = $this->em->find(Ship::class, 78);
        $rescuer->setUser($this->owner)->setLocation($this->cube->getLocation())->setFleet(null)->setDockedTo(null)->setMaxShield(100000);
        $rescuer->getBuildplan()->setCrew(0);
        $this->cube->getBuildplan()->setCrew(0);
        foreach ([$rescuer, $this->cube] as $ship) {
            foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::SHIELDS] as $type) {
                $this->system($ship, $type)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            }
            $this->system($ship, SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            $wrappers->wrapShip($ship)->getEpsSystemData()->setEps(100)->update();
        }
        $rescuer->getCondition()->setShield($rescuer->getMaxShield());
        $this->cube->getRump()->setPhaserVolleys(3);
        $this->em->flush();
        $report = new InformationWrapper();

        self::assertTrue($this->em->wrapInTransaction(fn (): bool => $container->get(BorgTractorRescueResponse::class)->respond($this->event, $this->cube, $rescuer, $report)));

        self::assertSame(100, $wrappers->wrapShip($rescuer)->getEpsSystemData()->getEps());
        self::assertLessThan(100, $wrappers->wrapShip($this->cube)->getEpsSystemData()->getEps());
        self::assertSame(3, substr_count($report->getInformationsAsString(), 'Die Testkubus feuert mit einem'));
        self::assertStringNotContainsString('Die Aerie Three feuert', $report->getInformationsAsString());
        self::assertLessThan($rescuer->getMaxShield(), $rescuer->getCondition()->getShield());
    }

    private function capture(): void
    {
        self::assertTrue($this->em->wrapInTransaction(fn (): bool => $this->service->tryCapture($this->event, $this->cube)));
    }

    private function expireEvent(): void
    {
        $configuration = $this->event->getConfiguration();
        $configuration->setEndTime($this->now - 1)->setSettings([...$configuration->getSettings(), 'transwarp_escape_bp' => 8000]);
        $this->event->setNextTickAt($this->now);
        $this->em->flush();
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturnUsing(fn (): int => $this->now);
        $departure = $this->getContainer()->make(BorgCompanionDeparture::class, ['assimilation' => new BorgAssimilationLifecycle(fn (): BorgAssimilationService => $this->service)]);
        $tick = $this->getContainer()->make(BorgSphereTick::class, ['departure' => $departure, 'time' => $clock]);
        $this->em->wrapInTransaction(fn () => $tick->work());
    }

    private function amount(): int
    {
        return $this->target->getStorage()->get(2)?->getAmount() ?? 0;
    }

    private function system(Ship $ship, SpacecraftSystemTypeEnum $type): SpacecraftSystem
    {
        if (!$ship->hasSpacecraftSystem($type)) {
            $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type);
            $ship->getSystems()->set($type->value, $system);
            $this->em->persist($system);
        }
        return $ship->getSpacecraftSystem($type)->setStatus(100);
    }
}
