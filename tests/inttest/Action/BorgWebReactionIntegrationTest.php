<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgWebReaction;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhase;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\User;

final class BorgWebReactionIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private BorgWebReaction $reaction;
    private Ship $cube;
    private TholianWeb $web;
    private BorgSphereEvent $event;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->cube = $this->em->find(Ship::class, 77);
        $container->get(LeaveFleetInterface::class)->leaveFleet($this->cube);
        $this->web = $this->em->find(TholianWeb::class, 60001);
        $this->cube->setUser($this->em->find(User::class, 10))->setLocation($this->web->getLocation())->setDockedTo(null)->setHoldingWeb($this->web)->setName('Testkubus');
        $this->web->getCapturedSpacecrafts()->add($this->cube);
        $this->cube->setMaxHull(100000)->setMaxShield(100000);
        $this->cube->getCondition()->setHull(100000)->setShield(100000)->setDisabled(false)->setIsDestroyed(false);
        $this->cube->getBuildplan()->setCrew(0);
        $this->cube->getRump()->setPhaserVolleys(2)->setTorpedoVolleys(0);
        if (!$this->cube->hasSpacecraftSystem(SpacecraftSystemTypeEnum::NBS)) {
            $system = new SpacecraftSystem()->setSpacecraft($this->cube)->setSystemType(SpacecraftSystemTypeEnum::NBS);
            $this->cube->getSystems()->set(SpacecraftSystemTypeEnum::NBS->value, $system);
            $this->em->persist($system);
        }
        foreach ($this->cube->getSystems() as $system) {
            $system->setStatus(100);
            if (in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER], true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_ON);
            } elseif (in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::WARPDRIVE, SpacecraftSystemTypeEnum::CLOAK, SpacecraftSystemTypeEnum::SHIELDS], true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
        }
        $weapon = $this->cube->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getModule()->getWeapon();
        $weapon->setCriticalChance(0)->setVariance(0)->setFiringMode(EnergyWeaponPhase::FIRINGMODE_FOCUS);
        $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->cube);
        $wrapper->getEpsSystemData()->setMaxEps(1000)->setEps(100)->update();
        $wrapper->getComputerSystemDataMandatory()->setHitChance(100)->setEvadeChance(0)->update();
        $wrapper->getEnergyWeaponSystemData()->setBaseDamage(50)->update();
        $this->web->getCondition()->setHull(5000)->setShield(0)->setDisabled(false)->setIsDestroyed(false);
        $this->web->setMaxHull(5000)->setMaxShield(0);
        $now = $container->get(StuTime::class)->time();
        $quest = new NPCQuest()->setUser($this->cube->getUser())->setUserId(10)->setTitle('Netzreaktion')->setStart($now - 60);
        $configuration = new BorgSphereConfiguration()->setId(2)->setUser($this->cube->getUser())->setQuest($quest)
            ->setColony($this->em->find(Colony::class, 42))->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))
            ->setEndTime($now + 86400)->setSettings(['assimilation_enabled' => 1]);
        $this->event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId($this->cube->getId())->setPhase(BorgSpherePhaseEnum::ROAMING)->setNextTickAt($now + 3600);
        $this->em->persist($quest);
        $this->em->persist($configuration);
        $this->em->persist($this->event);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(25);
        $this->reaction = $container->make(BorgWebReaction::class, ['random' => $random]);
        $this->em->flush();
        self::assertTrue($wrapper->canFire());
    }

    /** @return iterable<string, array{bool}> */
    public static function webSurvival(): iterable
    {
        yield 'web survives the direct response' => [false];
        yield 'web is destroyed and its links are cleared' => [true];
    }

    #[DataProvider('webSurvival')]
    public function testRealCombatDamagesTheWebWithoutRedirectingFireOntoTheCapturedCube(bool $destroy): void
    {
        if ($destroy) {
            $this->web->getCondition()->setHull(1);
        }
        $information = new InformationWrapper();
        $webId = $this->web->getId();

        $this->em->wrapInTransaction(fn () => $this->reaction->react($this->web, $information));

        $report = implode("\n", $information->getInformations());
        self::assertStringContainsString('Testkubus feuert', $report);
        self::assertStringContainsString('- Hüllenschaden:', $report);
        self::assertSame($destroy ? 1 : 2, substr_count($report, ' feuert '), $report);
        self::assertSame(100000, $this->cube->getCondition()->getHull(), $report);
        self::assertSame(100000, $this->cube->getCondition()->getShield(), $report);
        self::assertFalse($this->cube->getCondition()->isDisabled());
        self::assertFalse($this->cube->getCondition()->isDestroyed());
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $this->event->getPhase());
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->cube);
        self::assertSame($destroy ? 99 : 98, $wrapper->getEpsSystemData()->getEps());
        if (!$destroy) {
            self::assertLessThan(5000, $this->web->getCondition()->getHull());
            self::assertFalse($this->web->getCondition()->isDestroyed());
            self::assertSame($this->web, $this->cube->getHoldingWeb());
            self::assertSame($this->web, $this->em->find(TholianWeb::class, $webId));
            return;
        }
        self::assertTrue($this->web->getCondition()->isDestroyed());
        self::assertNull($this->cube->getHoldingWeb());
        self::assertNull($this->em->find(TholianWeb::class, $webId));
        foreach ([1024, 1025] as $spinnerId) {
            $spinner = $this->em->find(Ship::class, $spinnerId);
            $emitter = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($spinner)->getWebEmitterSystemData();
            self::assertNull($emitter->getWebUnderConstruction());
        }
        $owner = $this->em->find(Ship::class, 1024);
        self::assertNull($this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($owner)->getWebEmitterSystemData()->getOwnedTholianWeb());
    }
}
