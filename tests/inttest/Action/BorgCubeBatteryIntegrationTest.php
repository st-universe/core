<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgCubeBattery;
use Stu\Component\Borg\BorgSphereCombat;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackPreparationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhase;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;

final class BorgCubeBatteryIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private BorgSphereConfiguration $configuration;
    private BorgCubeBattery $previousBattery;
    private Ship $cube;
    private SpacecraftWrapperInterface $cubeWrapper;
    private SpacecraftWrapperInterface $playerWrapper;
    private SpacecraftAttackCycleInterface $cycle;
    private AttackerProviderFactoryInterface $providers;
    private int $now;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->now = $container->get(StuTime::class)->time();
        $player = $this->em->find(Ship::class, 42);
        $this->cube = $this->em->find(Ship::class, 77);
        $this->cube->setUser($this->em->find(User::class, 10))->setName('Testkubus');
        $player->setName('Testangreifer');
        $this->playerWrapper = $this->prepareShip($player);
        $this->cubeWrapper = $this->prepareShip($this->cube);
        $this->cube->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->setStatus(10);
        $this->cubeWrapper->getEpsSystemData()->setMaxEps(20)->setEps(0)->setBattery(7)->setBatteryCooldown($this->now + 86400)->update();
        $this->createEvent();
        $this->em->flush();
        $this->previousBattery = $container->get(BorgCubeBattery::class);
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturn($this->now);
        $container->set(BorgCubeBattery::class, $container->make(BorgCubeBattery::class, ['combat' => $container->make(BorgSphereCombat::class), 'time' => $clock]));
        $this->providers = $container->make(AttackerProviderFactoryInterface::class);
        $preparation = $this->mock(SpacecraftAttackPreparationInterface::class);
        $preparation->shouldReceive('getReady');
        $this->cycle = $container->make(SpacecraftAttackCycleInterface::class, ['attackerProviderFactory' => $this->providers, 'spacecraftAttackPreparation' => $preparation]);
    }

    #[Override]
    public function tearDown(): void
    {
        $this->getContainer()->set(BorgCubeBattery::class, $this->previousBattery);
        parent::tearDown();
    }

    /** @return iterable<string, array{int, int, int, int, int, int}> */
    public static function reserveLimits(): iterable
    {
        yield 'empty EPS can return every salvo while reserve remains' => [7, 4, 4, 2, 1, 60];
        yield 'salvos stop exactly when the finite reserve is exhausted' => [7, 8, 7, 0, 0, 60];
        yield 'the last reserve unit permits exactly one countershot' => [1, 8, 1, 0, 0, 60];
        yield 'an empty reserve grants no energy and preserves cooldown' => [0, 8, 0, 0, 0, 86400];
    }

    #[DataProvider('reserveLimits')]
    public function testNormalIncomingFireUsesOnlyAvailableReserveAndPersistsTheDamagedEpsCapacity(int $battery, int $volleys, int $shots, int $eps, int $remainingBattery, int $cooldown): void
    {
        $this->cube->getRump()->setPhaserVolleys($volleys);
        $this->cubeWrapper->getEpsSystemData()->setBattery($battery)->update();
        self::assertFalse($this->cubeWrapper->canFire());

        $report = $this->fight();

        self::assertSame($shots, substr_count($report, 'Die Testkubus feuert '), $report);
        self::assertSame(2, $this->cubeWrapper->getEpsSystemData()->getMaxEps());
        self::assertSame($battery, $eps + $remainingBattery + $shots);
        $this->assertPersistedEnergy($eps, $remainingBattery, $this->now + $cooldown);
    }

    public function testBatteryScopeEndsBeforeLaterEnergyUseOrAnOffensiveBattle(): void
    {
        $this->cube->getRump()->setPhaserVolleys(4);
        $this->fight();
        self::assertSame(1, $this->cubeWrapper->getEpsSystemData()->getBattery());

        $this->providers->createSpacecraftAttacker($this->cubeWrapper)->reduceEps(1);

        self::assertSame(1, $this->cubeWrapper->getEpsSystemData()->getEps());
        self::assertSame(1, $this->cubeWrapper->getEpsSystemData()->getBattery());
        $report = $this->fight(cubeAttacks: true);
        self::assertSame(1, substr_count($report, 'Die Testkubus feuert '), $report);
        self::assertSame(0, $this->cubeWrapper->getEpsSystemData()->getEps());
        self::assertSame(1, $this->cubeWrapper->getEpsSystemData()->getBattery());
        self::assertSame($this->now + 60, $this->cubeWrapper->getEpsSystemData()->getBatteryCooldown());
    }

    /** @return iterable<string, array{SpacecraftAttackCauseEnum}> */
    public static function specialBattles(): iterable
    {
        yield 'tractor rescue response' => [SpacecraftAttackCauseEnum::BORG_TRACTOR_RESCUE_RESPONSE];
        yield 'energy web response' => [SpacecraftAttackCauseEnum::BORG_WEB_REACTION];
    }

    #[DataProvider('specialBattles')]
    public function testSpecialBattlesDoNotUnloadTheCubeBattery(SpacecraftAttackCauseEnum $cause): void
    {
        $report = $this->fight($cause);

        self::assertSame(0, substr_count($report, 'Die Testkubus feuert '), $report);
        $this->assertPersistedEnergy(0, 7, $this->now + 86400);
    }

    public function testAnEventSphereDoesNotReceiveTheCubeBatteryBehavior(): void
    {
        $this->configuration->setSettings(['assimilation_enabled' => 0]);

        $report = $this->fight();

        self::assertSame(0, substr_count($report, 'Die Testkubus feuert '), $report);
        $this->assertPersistedEnergy(0, 7, $this->now + 86400);
    }

    private function prepareShip(Ship $ship): SpacecraftWrapperInterface
    {
        $ship->getFleet()?->getShips()->toArray();
        $ship->setFleet(null)->setIsFleetLeader(false)->setDockedTo(null)->setLocation($this->em->find(Colony::class, 42)->getStarsystemMap());
        $ship->getBuildplan()->setCrew(0);
        $ship->setMaxHull(100000)->setMaxShield(100000);
        $ship->getCondition()->setHull(100000)->setShield(100000)->setDisabled(false)->setIsDestroyed(false);
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::SHIELDS] as $type) {
            if (!$ship->hasSpacecraftSystem($type)) {
                $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type);
                if ($type === SpacecraftSystemTypeEnum::SHIELDS) {
                    $system->setModule($this->em->find(Module::class, 10202))->setData('{"shieldRegenerationTimer":0}');
                }
                $ship->getSystems()->set($type->value, $system);
                $this->em->persist($system);
            }
        }
        foreach ($ship->getSystems() as $system) {
            $system->setStatus(100)->setMode(in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::SHIELDS], true) ? SpacecraftSystemModeEnum::MODE_ON : SpacecraftSystemModeEnum::MODE_OFF);
        }
        $ship->getRump()->setPhaserVolleys(8)->setTorpedoVolleys(0);
        $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getModule()->getWeapon()->setCriticalChance(0)->setVariance(0)->setFiringMode(EnergyWeaponPhase::FIRINGMODE_FOCUS);
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($ship);
        $wrapper->getEpsSystemData()->setMaxEps(100)->setEps(100)->setBattery(0)->update();
        $wrapper->getComputerSystemDataMandatory()->setHitChance(100)->setEvadeChance(0)->update();
        $wrapper->getEnergyWeaponSystemData()->setBaseDamage(50)->update();
        return $wrapper;
    }

    private function createEvent(): void
    {
        $owner = $this->cube->getUser();
        $quest = new NPCQuest()->setUser($owner)->setUserId($owner->getId())->setTitle('Batterietest')->setStart($this->now - 100);
        $this->configuration = new BorgSphereConfiguration()->setId(1)->setColony($this->em->find(Colony::class, 42))->setQuest($quest)->setUser($owner)
            ->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))->setEndTime($this->now + 86400)->setSettings(['assimilation_enabled' => 1, 'tick_seconds' => 1800, 'transwarp_initial_bp' => 1000]);
        $event = new BorgSphereEvent()->setConfiguration($this->configuration)->setShipId($this->cube->getId())->setPhase(BorgSpherePhaseEnum::ROAMING);
        $this->em->persist($quest);
        $this->em->persist($this->configuration);
        $this->em->persist($event);
    }

    private function fight(SpacecraftAttackCauseEnum $cause = SpacecraftAttackCauseEnum::SHIP_FIGHT, bool $cubeAttacks = false): string
    {
        $this->em->flush();
        $parties = $this->getContainer()->get(BattlePartyFactoryInterface::class);
        $attackers = $parties->createSingletonBattleParty($cubeAttacks ? $this->cubeWrapper : $this->playerWrapper);
        $defenders = $parties->createSingletonBattleParty($cubeAttacks ? $this->playerWrapper : $this->cubeWrapper);
        $messages = $this->em->wrapInTransaction(fn () => $this->cycle->cycle($attackers, $defenders, $cause));
        return implode("\n", $messages->getInformationDump()->getInformations());
    }

    private function assertPersistedEnergy(int $eps, int $battery, int $cooldown): void
    {
        $id = $this->cube->getId();
        $this->em->flush();
        $this->em->clear();
        $data = json_decode($this->em->find(Ship::class, $id)->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getData(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($eps, $data['eps']);
        self::assertSame($battery, $data['battery']);
        self::assertSame($cooldown, $data['batteryCooldown']);
    }
}
