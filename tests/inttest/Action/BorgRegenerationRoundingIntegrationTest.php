<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgSphereActivityPolicy;
use Stu\Component\Borg\BorgSphereAttackContext;
use Stu\Component\Borg\BorgSphereCombat;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgSphereRegeneration;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\Damage\SystemDamage;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;

final class BorgRegenerationRoundingIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Ship $ship;
    private BorgSphereEvent $event;
    private BorgSphereRegeneration $regeneration;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $now = $container->get(StuTime::class)->time();
        $owner = $this->em->find(User::class, 10);
        $quest = new NPCQuest()->setUser($owner)->setUserId($owner->getId())->setTitle('Borg-Regenerationstest')->setStart($now - 100);
        $configuration = new BorgSphereConfiguration()->setId(1)->setColony($this->em->find(Colony::class, 42))->setQuest($quest)->setUser($owner)
            ->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))->setEndTime($now + 86400)->setSettings([
                'assimilation_enabled' => 1,
                'tick_seconds' => 1800,
                'normal_regen_min_percent' => 2,
                'normal_regen_max_percent' => 10,
                'shield_regen_min_percent' => 1,
                'shield_regen_max_percent' => 5,
                'transwarp_regen_bp' => 101,
                'transwarp_escape_bp' => 8000,
                'transwarp_floor_step_bp' => 1000,
                'wake_percent' => 10
            ]);
        $this->ship = $this->em->find(Ship::class, 42);
        $this->ship->setMaxHull(1001)->setMaxShield(2003);
        $this->ship->getCondition()->setHull(100)->setShield(200)->setDisabled(true);
        foreach ([SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::TRANSWARP_COIL] as $type) {
            if (!$this->ship->hasSpacecraftSystem($type)) {
                $system = new SpacecraftSystem()->setSpacecraft($this->ship)->setSystemType($type);
                $this->ship->getSystems()->set($type->value, $system);
                $this->em->persist($system);
            }
        }
        foreach ($this->ship->getSystems() as $system) {
            $system->setStatus(10)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        $this->event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId($this->ship->getId())->setFirstDisabledAt($now)
            ->setPhase(BorgSpherePhaseEnum::REGENERATING)->setNextTickAt($now + 1800)->setTranswarpProgress(1000)->setTranswarpFloor(1000);
        $this->em->persist($quest);
        $this->em->persist($configuration);
        $this->em->persist($this->event);
        $this->em->flush();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->with(2, 10)->andReturn(5);
        $random->shouldReceive('rand')->with(1, 5)->andReturn(1);
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturn(strtotime('2026-11-16 12:00:00'));
        $this->regeneration = new BorgSphereRegeneration($random, new BorgSphereActivityPolicy($clock));
    }

    public function testEveryTickRoundsUpIndependentlyAcrossDatabaseReloads(): void
    {
        $this->regeneration->regenerate($this->event, $this->ship, 1);
        $this->reload();

        self::assertSame(126, $this->ship->getCondition()->getHull());
        self::assertSame(211, $this->ship->getCondition()->getShield());
        self::assertSame(13, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(1051, $this->event->getTranswarpProgress());

        $this->regeneration->regenerate($this->event, $this->ship, 1);
        $this->reload();

        self::assertSame(152, $this->ship->getCondition()->getHull());
        self::assertSame(222, $this->ship->getCondition()->getShield());
        self::assertSame(16, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(1102, $this->event->getTranswarpProgress());
    }

    public function testRoundedHealingRemainsCappedAfterDatabaseReload(): void
    {
        $this->ship->getCondition()->setHull(1000)->setShield(2002);
        foreach ($this->ship->getSystems() as $system) {
            $system->setStatus(99);
        }
        $this->event->setTranswarpProgress(7990);

        $this->regeneration->regenerate($this->event, $this->ship, 1);
        $this->reload();

        self::assertSame(1001, $this->ship->getCondition()->getHull());
        self::assertSame(2003, $this->ship->getCondition()->getShield());
        self::assertSame(100, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(8000, $this->event->getTranswarpProgress());
        self::assertSame(7000, $this->event->getTranswarpFloor());
    }

    public function testRepeatedlyDestroyedSensorsAndWeaponsReactivateOnAnAlreadyActiveCubeAcrossReloads(): void
    {
        $container = $this->getContainer();
        $types = [SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO];
        foreach ($types as $type) {
            if (!$this->ship->hasSpacecraftSystem($type)) {
                $system = new SpacecraftSystem()->setSpacecraft($this->ship)->setSystemType($type);
                $this->ship->getSystems()->set($type->value, $system);
                $this->em->persist($system);
            }
        }
        $this->ship->getCondition()->setDisabled(false);
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING);
        $context = new BorgSphereAttackContext();
        $combat = $container->make(BorgSphereCombat::class, ['context' => $context]);
        $damage = $container->make(SystemDamage::class, ['borgSphereCombat' => $combat]);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->ship);
            $context->begin($this->ship, true, false);
            try {
                $this->em->wrapInTransaction(fn () => $combat->withLockedEvent($this->ship, $this->event, function () use ($damage, $wrapper, $types): void {
                    foreach ($types as $type) {
                        $damage->damageShipSystem($wrapper, $this->ship->getSpacecraftSystem($type), 100, new InformationWrapper());
                    }
                }));
            } finally {
                $context->clear();
            }
            $this->reload();
            foreach ($types as $type) {
                self::assertSame(0, $this->ship->getSpacecraftSystem($type)->getStatus());
                self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $this->ship->getSpacecraftSystem($type)->getMode());
            }

            $this->regeneration->regenerate($this->event, $this->ship, 1);
            $this->reload();

            self::assertFalse($this->ship->getCondition()->isDisabled());
            self::assertSame(BorgSpherePhaseEnum::ROAMING, $this->event->getPhase());
            foreach ($types as $type) {
                self::assertSame(3, $this->ship->getSpacecraftSystem($type)->getStatus());
                self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $this->ship->getSpacecraftSystem($type)->getMode());
            }
        }
    }

    private function reload(): void
    {
        $eventId = $this->event->getId();
        $shipId = $this->ship->getId();
        $this->em->flush();
        $this->em->clear();
        $this->event = $this->em->find(BorgSphereEvent::class, $eventId);
        $this->ship = $this->em->find(Ship::class, $shipId);
    }
}
