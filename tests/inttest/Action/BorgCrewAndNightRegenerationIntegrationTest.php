<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Override;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgSphereActivityPolicy;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgSphereRegeneration;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Tick\Process\BorgSphereTick;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

final class BorgCrewAndNightRegenerationIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Ship $ship;
    private BorgSphereEvent $event;
    private StuTime $clock;
    private StuRandom&MockInterface $random;
    private int $now;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->now = strtotime('2026-11-17 09:30:00');
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturnUsing(fn (): int => $this->now);
        $this->clock = $clock;
        $this->random = $this->mock(StuRandom::class);
        $this->random->shouldReceive('rand')->with(2, 10)->andReturn(10)->byDefault();
        $this->random->shouldReceive('rand')->with(1, 5)->andReturn(5)->byDefault();
        $owner = $this->em->find(User::class, 10);
        $colony = $this->em->find(Colony::class, 42);
        $this->ship = $this->em->find(Ship::class, 77);
        $this->ship->setUser($owner)->setLocation($colony->getStarsystemMap())->setFleet(null)->setDockedTo(null)->setMaxHull(1000)->setMaxShield(2000);
        $this->ship->getBuildplan()->setCrew($this->ship->getCrewCount());
        $this->ship->getCondition()->setHull(320)->setShield(800)->setDisabled(false)->setIsDestroyed(false);
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::TRANSWARP_COIL] as $type) {
            if (!$this->ship->hasSpacecraftSystem($type)) {
                $system = new SpacecraftSystem()->setSpacecraft($this->ship)->setSystemType($type);
                $this->ship->getSystems()->set($type->value, $system);
                $this->em->persist($system);
            }
        }
        foreach ($this->ship->getSystems() as $system) {
            $system->setStatus(32)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->setStatus(40);
        $this->clearSector();
        $quest = new NPCQuest()->setUser($owner)->setUserId($owner->getId())->setTitle('Borg-Crew und Nachtregeneration')->setStart($this->now - 3600);
        $configuration = new BorgSphereConfiguration()->setId(1)->setColony($colony)->setQuest($quest)->setUser($owner)
            ->setBuildplan($this->ship->getBuildplan())->setEndTime($this->now + 86400)->setSettings([
                'assimilation_enabled' => 1, 'tick_seconds' => 1800, 'idle_chance_percent' => 100,
                'intercept_chance_percent' => 40, 'movement_min_fields' => 1, 'movement_max_fields' => 7,
                'normal_regen_min_percent' => 2, 'normal_regen_max_percent' => 10,
                'shield_regen_min_percent' => 1, 'shield_regen_max_percent' => 5,
                'transwarp_regen_bp' => 100, 'transwarp_escape_bp' => 8000, 'transwarp_initial_bp' => 1000,
                'transwarp_floor_step_bp' => 1000, 'wake_percent' => 10, 'nanite_commodity_id' => 2, 'roam_radius' => 20
            ]);
        $this->event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId($this->ship->getId())
            ->setFirstDisabledAt($this->now - 3600)->setPhase(BorgSpherePhaseEnum::ROAMING)->setNextTickAt($this->now)
            ->setTranswarpProgress(3250)->setTranswarpFloor(3000);
        $this->em->persist($quest);
        $this->em->persist($configuration);
        $this->em->persist($this->event);
        $this->em->flush();
    }

    public function testMissingCrewIsPresentBeforeRegenerationAndIsNotDuplicatedOnTheNextTick(): void
    {
        $this->now = strtotime('2026-11-17 12:00:00');
        $this->event->setNextTickAt($this->now);
        $captain = $this->getContainer()->get(CrewCreatorInterface::class)->create(10)
            ->setSpacecraft($this->ship)->setSlot(CrewTypeEnum::CAPTAIN);
        $captain->getCrew()->setName('Bestehender Kommandant');
        $this->ship->getCrewAssignments()->add($captain);
        $minimum = $this->ship->getCrewCount() + 2;
        $this->ship->getBuildplan()->setCrew($minimum);
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER] as $type) {
            $this->ship->getSpacecraftSystem($type)->setStatus(0)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        $this->em->flush();
        $captainId = $captain->getCrew()->getId();
        $existingIds = $this->ship->getCrewAssignments()->map(static fn (CrewAssignment $assignment): int => $assignment->getCrew()->getId())->toArray();
        $crewBefore = $this->em->getRepository(Crew::class)->count([]);
        $this->random->shouldReceive('rand')->with(2, 10)->atLeast()->once()->andReturnUsing(function () use ($minimum): int {
            self::assertSame($minimum, $this->ship->getCrewCount());
            self::assertTrue($this->ship->hasEnoughCrew());
            return 10;
        });

        $this->runTick();
        $this->reload();

        self::assertSame($minimum, $this->ship->getCrewCount());
        self::assertSame($crewBefore + 2, $this->em->getRepository(Crew::class)->count([]));
        $restoredCaptain = $this->ship->getCrewAssignments()->findFirst(static fn (int $key, CrewAssignment $assignment): bool => $assignment->getCrew()->getId() === $captainId);
        self::assertNotNull($restoredCaptain);
        self::assertSame(CrewTypeEnum::CAPTAIN, $restoredCaptain->getSlot());
        self::assertSame('Bestehender Kommandant', $restoredCaptain->getCrew()->getName());
        $newCrew = $this->ship->getCrewAssignments()->filter(static fn (CrewAssignment $assignment): bool => !in_array($assignment->getCrew()->getId(), $existingIds, true));
        self::assertCount(2, $newCrew);
        foreach ($newCrew as $assignment) {
            self::assertSame(10, $assignment->getUser()->getId());
            self::assertSame(10, $assignment->getCrew()->getUser()->getId());
            self::assertSame(CrewTypeEnum::CREWMAN, $assignment->getSlot());
        }
        self::assertTrue($this->ship->getNbs());
        self::assertTrue($this->ship->getPhaserState());
        $this->now = $this->event->getNextTickAt();

        $this->runTick();
        $this->reload();

        self::assertSame($minimum, $this->ship->getCrewCount());
        self::assertSame($crewBefore + 2, $this->em->getRepository(Crew::class)->count([]));
    }

    public function testNightCapPreservesHigherValuesAndDaytimeRegenerationContinuesAfterReload(): void
    {
        $this->runTick();
        $this->reload();

        self::assertSame(330, $this->ship->getCondition()->getHull());
        self::assertSame(800, $this->ship->getCondition()->getShield());
        self::assertSame(33, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getStatus());
        self::assertSame(40, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getStatus());
        self::assertSame(3300, $this->event->getTranswarpProgress());
        self::assertSame(33, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());
        self::assertSame(3000, $this->event->getTranswarpFloor());
        $this->now = $this->event->getNextTickAt();
        self::assertSame('10:00:00', date('H:i:s', $this->now));

        $this->runTick();
        $this->reload();

        self::assertSame(380, $this->ship->getCondition()->getHull());
        self::assertSame(850, $this->ship->getCondition()->getShield());
        self::assertSame(38, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getStatus());
        self::assertSame(45, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getStatus());
        self::assertSame(3350, $this->event->getTranswarpProgress());
    }

    private function clearSector(): void
    {
        $location = $this->ship->getLocation();
        $other = $this->em->createQueryBuilder()->select('field')->from(StarSystemMap::class, 'field')
            ->where('field.id <> :location')->setParameter('location', $location->getId())->setMaxResults(1)->getQuery()->getSingleResult();
        foreach ($location->getSpacecrafts()->toArray() as $spacecraft) {
            if ($spacecraft !== $this->ship) {
                $spacecraft->setLocation($other);
            }
        }
        $location->getSpacecrafts()->clear();
        $location->getSpacecrafts()->add($this->ship);
    }

    private function runTick(): void
    {
        $this->em->flush();
        $regeneration = new BorgSphereRegeneration($this->random, new BorgSphereActivityPolicy($this->clock));
        $tick = $this->getContainer()->make(BorgSphereTick::class, ['regeneration' => $regeneration, 'time' => $this->clock]);
        $this->em->wrapInTransaction(fn () => $tick->work());
    }

    private function reload(): void
    {
        $shipId = $this->ship->getId();
        $eventId = $this->event->getId();
        $this->em->clear();
        $this->ship = $this->em->find(Ship::class, $shipId);
        $this->event = $this->em->find(BorgSphereEvent::class, $eventId);
    }
}
