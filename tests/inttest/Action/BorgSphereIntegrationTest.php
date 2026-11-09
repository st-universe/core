<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Borg\BorgSphereAttackContext;
use Stu\Component\Borg\BorgSphereBattle;
use Stu\Component\Borg\BorgSphereBehavior;
use Stu\Component\Borg\BorgSphereCombat;
use Stu\Component\Borg\BorgSphereFlight;
use Stu\Component\Borg\BorgSphereInterception;
use Stu\Component\Borg\BorgSphereNavigation;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgSphereRaidService;
use Stu\Component\Borg\BorgSphereRaidTeam;
use Stu\Component\Borg\BorgSphereRegeneration;
use Stu\Component\Borg\BorgSphereSpawner;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Action\AttackSpacecraft\AttackSpacecraft;
use Stu\Module\Spacecraft\Action\CollectBorgNanites\CollectBorgNanites;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertDetectionInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacade;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\SkipDetectionInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\IncomingBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycle;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackPreparationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhase;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhase;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamage;
use Stu\Module\Spacecraft\Lib\Damage\SystemDamage;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCore;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\View\ShowBorgSphere\ShowBorgSphere;
use Stu\Module\Tick\Process\BorgSphereTick;
use Stu\Module\Twig\TwigHelper;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyType;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\BorgSphereRaid;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Twig\Environment;

final class BorgSphereIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private BorgSphereRepositoryInterface $events;
    private BorgSphereConfiguration $configuration;
    private BorgSphereEvent $event;
    private Ship $source;
    private Ship $sphere;
    private int $now;

    public function testEventSphereDoesNotAutomaticallyAttackIncomingPlayers(): void
    {
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING);
        $this->sphere->getCondition()->setDisabled(false);
        $this->em->flush();
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->sphere);
        self::assertTrue($this->getContainer()->get(SkipDetectionInterface::class)->isSkipped($this->source, $wrapper, null, new ArrayCollection()));
    }

    /** @return iterable<string, array{bool}> */
    public static function automaticBattleOutcomes(): iterable
    {
        yield 'all local enemy ships are attacked' => [false];
        yield 'counterfire disables sphere after first target' => [true];
    }

    #[DataProvider('automaticBattleOutcomes')]
    public function testAutomaticAttackIsTheOnlyActionAndStopsIfSphereIsDisabled(bool $disabled): void
    {
        $container = $this->getContainer();
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING);
        $this->sphere->getCondition()->setDisabled(false);
        $this->sphere->getBuildplan()->setCrew($this->sphere->getCrewCount());
        $this->combatWrapper($this->sphere, 100);
        $this->combatWrapper($this->source, 100);
        $other = $this->em->find(Ship::class, 78)->setUser($this->source->getUser())->setLocation($this->sphere->getLocation());
        $this->combatWrapper($other, 100);
        $other->getCondition()->setDisabled(false)->setIsDestroyed(false);
        $location = $this->sphere->getLocation();
        $location->getSpacecrafts()->clear();
        foreach ([$this->sphere, $this->source, $other] as $ship) {
            $location->getSpacecrafts()->set($ship->getId(), $ship);
        }
        $this->em->flush();
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $attacked = [];
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldReceive('attack')->times($disabled ? 1 : 2)->andReturnUsing(
            function (SpacecraftWrapperInterface $source, SpacecraftWrapperInterface $target) use (&$attacked, $disabled, $location): bool {
                self::assertSame($this->sphere, $source->get());
                self::assertSame($location, $source->get()->getLocation());
                self::assertSame($location, $target->get()->getLocation());
                $attacked[] = $target->get()->getId();
                if ($disabled) {
                    $this->sphere->getCondition()->setDisabled(true);
                    $this->event->setPhase(BorgSpherePhaseEnum::REGENERATING);
                }
                return true;
            }
        );
        $battle = $container->make(BorgSphereBattle::class, ['attacks' => $attacks, 'relations' => $relations]);
        $behavior = $container->make(BorgSphereBehavior::class, ['battle' => $battle]);

        $this->em->wrapInTransaction(fn () => $behavior->work($this->event, $this->sphere));

        self::assertSame($disabled ? [$this->source->getId()] : [$this->source->getId(), $other->getId()], $attacked);
        self::assertFalse($container->get(BorgSphereAttackContext::class)->isBattleStopped());
        self::assertSame($location, $this->sphere->getLocation());
    }

    public function testTheFirstRegisteredAttackEndsTheApproachBeforeDamageIsApplied(): void
    {
        $this->event->setPhase(BorgSpherePhaseEnum::APPROACH);
        $this->sphere->getCondition()->setDisabled(false);
        $hull = $this->sphere->getCondition()->getHull();
        $this->em->flush();
        $combat = $this->getContainer()->make(BorgSphereCombat::class, ['context' => new BorgSphereAttackContext()]);

        self::assertTrue($this->em->wrapInTransaction(fn () => $combat->registerHit($this->sphere)));

        self::assertSame(BorgSpherePhaseEnum::ROAMING, $this->event->getPhase());
        self::assertSame($hull, $this->sphere->getCondition()->getHull());
    }

    public function testAutomaticActionsWaitUntilTheHourlyDeadlineAndDoNotRepeatWithinTheSameHour(): void
    {
        $container = $this->getContainer();
        $now = $this->now;
        $deadline = $now + 3600;
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING)->setFirstDisabledAt(null)->setNextTickAt($deadline);
        $this->sphere->getCondition()->setDisabled(false);
        $this->sphere->getBuildplan()->setCrew($this->sphere->getCrewCount());
        $this->combatWrapper($this->sphere, 100);
        $this->combatWrapper($this->source, 100);
        $location = $this->sphere->getLocation();
        $location->getSpacecrafts()->clear();
        foreach ([$this->sphere, $this->source] as $ship) {
            $location->getSpacecrafts()->set($ship->getId(), $ship);
        }
        $this->em->flush();
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attempts = 0;
        $attacks->shouldReceive('attack')->once()->andReturnUsing(static function () use (&$attempts): bool {
            $attempts++;
            return true;
        });
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $battle = $container->make(BorgSphereBattle::class, ['attacks' => $attacks, 'relations' => $relations]);
        $behavior = $container->make(BorgSphereBehavior::class, ['battle' => $battle]);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturnUsing(static function () use (&$now): int {
            return $now;
        });
        $tick = $container->make(BorgSphereTick::class, ['behavior' => $behavior, 'time' => $time]);

        $this->em->wrapInTransaction(fn () => $tick->work());
        self::assertSame(0, $attempts);
        self::assertSame($location, $this->sphere->getLocation());
        $now = $deadline;
        $this->em->wrapInTransaction(fn () => $tick->work());
        self::assertSame(1, $attempts);
        self::assertSame($deadline + 3600, $this->event->getNextTickAt());
        $this->em->wrapInTransaction(fn () => $tick->work());
        self::assertSame(1, $attempts);
    }

    public function testCounterfireCannotResetHourlyRegenerationOrTranswarpProgress(): void
    {
        $container = $this->getContainer();
        $deadline = $this->now + 3600;
        $now = $deadline;
        foreach ([SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::TRANSWARP_COIL] as $type) {
            if (!$this->sphere->hasSpacecraftSystem($type)) {
                $system = new SpacecraftSystem()->setSpacecraft($this->sphere)->setSystemType($type);
                $this->sphere->getSystems()->set($type->value, $system);
                $this->em->persist($system);
            }
        }
        $this->combatWrapper($this->sphere, 10);
        $this->combatWrapper($this->source, 100);
        $this->sphere->getBuildplan()->setCrew($this->sphere->getCrewCount());
        $maxHull = $this->sphere->getMaxHull();
        $maxShield = $this->sphere->getMaxShield(true);
        $hull = (int) ceil($maxHull / 10);
        $shield = (int) ceil($maxShield / 10);
        $this->sphere->getCondition()->setHull($hull)->setShield($shield);
        $this->event->setNextTickAt($deadline);
        $location = $this->sphere->getLocation();
        $location->getSpacecrafts()->clear();
        foreach ([$this->sphere, $this->source] as $ship) {
            $location->getSpacecrafts()->set($ship->getId(), $ship);
        }
        $this->em->flush();
        $combat = $container->make(BorgSphereCombat::class);
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldReceive('attack')->twice()->andReturnUsing(static function (SpacecraftWrapperInterface $attacker) use ($combat): bool {
            $combat->blocksDamage($attacker->get());
            return true;
        });
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $battle = $container->make(BorgSphereBattle::class, ['attacks' => $attacks, 'relations' => $relations]);
        $behavior = $container->make(BorgSphereBehavior::class, ['battle' => $battle]);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->with(2, 10)->times(2 * (count($this->sphere->getSystems()) + 1))->andReturn(2);
        $regeneration = $container->make(BorgSphereRegeneration::class, ['random' => $random]);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturnUsing(static function () use (&$now): int {
            return $now;
        });
        $tick = $container->make(BorgSphereTick::class, ['behavior' => $behavior, 'regeneration' => $regeneration, 'time' => $time, 'combat' => $combat]);

        foreach ([1, 2] as $hour) {
            $now = $deadline + ($hour - 1) * 3600;
            $this->em->wrapInTransaction(fn () => $tick->work());
            $this->em->refresh($this->event);
            self::assertSame($now + 3600, $this->event->getNextTickAt());
            self::assertSame(BorgSpherePhaseEnum::ROAMING, $this->event->getPhase());
            self::assertSame(1000 + $hour * 100, $this->event->getTranswarpProgress());
            self::assertSame($hull + $hour * (int) ceil($maxHull * 2 / 100), $this->sphere->getCondition()->getHull());
            self::assertSame($shield + $hour * (int) ceil($maxShield * 2 / 100), $this->sphere->getCondition()->getShield());
            foreach ($this->sphere->getSystems() as $system) {
                self::assertSame(10 + $hour * ($system->getSystemType() === SpacecraftSystemTypeEnum::TRANSWARP_COIL ? 1 : 2), $system->getStatus());
            }
            $now += 900;
            $this->em->wrapInTransaction(fn () => $tick->work());
            self::assertSame($hull + $hour * (int) ceil($maxHull * 2 / 100), $this->sphere->getCondition()->getHull());
        }
    }

    /** @return iterable<string, array{bool}> */
    public static function torpedoSupplyStates(): iterable
    {
        yield 'remaining torpedoes' => [false];
        yield 'all torpedoes fired' => [true];
    }

    #[DataProvider('torpedoSupplyStates')]
    public function testHourlyRegenerationRestoresDamagedEnergyCapacityAndTorpedoStocks(bool $empty): void
    {
        $container = $this->getContainer();
        $this->sphere->getRump()->setBaseTorpedoStorage(20);
        $wrapper = $this->combatWrapper($this->sphere, 10);
        $eps = $wrapper->getEpsSystemData();
        $warp = $wrapper->getWarpDriveSystemData();
        $reactor = $wrapper->getReactorWrapper();
        self::assertNotNull($eps);
        self::assertNotNull($warp);
        self::assertNotNull($reactor);
        $eps->setMaxEps(108)->update();
        $warp->setMaxWarpDrive(58)->update();
        $settings = $this->configuration->getSettings();
        $settings['idle_chance_percent'] = 100;
        $this->configuration->setSettings($settings);
        $this->sphere->getLocation()->getSpacecrafts()->clear();
        $this->sphere->getLocation()->getSpacecrafts()->add($this->sphere);
        $type = $this->em->getRepository(TorpedoType::class)->findOneBy(['level' => $this->sphere->getRump()->getTorpedoLevel()]);
        self::assertNotNull($type);
        $torpedoManager = $container->get(ShipTorpedoManagerInterface::class);
        $torpedoManager->changeTorpedo($wrapper, 7, $type);
        $now = $this->now;
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturnUsing(static function () use (&$now): int {
            return $now;
        });
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->with(2, 10)->andReturn(2);
        $regeneration = $container->make(BorgSphereRegeneration::class, ['random' => $random]);
        $tick = $container->make(BorgSphereTick::class, ['regeneration' => $regeneration, 'time' => $time]);
        for ($hour = 1; $hour <= 2; $hour++) {
            if ($empty || $hour === 2) {
                $torpedoManager->changeTorpedo($wrapper, -$this->sphere->getTorpedoCount());
                self::assertSame(0, $this->sphere->getTotalTorpedoCount());
                self::assertFalse($this->sphere->getTorpedoState());
            }
            $eps->setEps(0)->setBattery(0)->update();
            $warp->setWarpDrive(0)->update();
            $reactor->setLoad(0);
            $now = $this->now + $hour * 3600;
            $this->em->flush();

            $this->em->wrapInTransaction(fn () => $tick->work());

            foreach ([SpacecraftSystemTypeEnum::EPS, SpacecraftSystemTypeEnum::WARPDRIVE, $reactor->get()->getSystemType()] as $systemType) {
                $this->em->refresh($this->sphere->getSpacecraftSystem($systemType));
            }
            $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->sphere);
            $eps = $wrapper->getEpsSystemData();
            $warp = $wrapper->getWarpDriveSystemData();
            $reactor = $wrapper->getReactorWrapper();
            self::assertSame((int) ceil(108 * (10 + $hour * 2) / 100), $eps->getEps());
            self::assertSame($eps->getMaxEps(), $eps->getEps());
            self::assertSame($eps->getMaxBattery(), $eps->getBattery());
            self::assertSame($warp->getMaxWarpdrive(), $warp->getWarpDrive());
            self::assertSame((int) ceil(58 * (10 + $hour * 2) / 100), $warp->getWarpDrive());
            self::assertSame($reactor->getCapacity(), $reactor->getLoad());
            $storage = $this->sphere->getTorpedoStorage();
            self::assertNotNull($storage);
            $this->em->refresh($storage->getStorage());
            self::assertSame($this->sphere->getMaxTorpedos(), $this->sphere->getTotalTorpedoCount());
            self::assertSame($this->sphere->getMaxTorpedos(), $storage->getStorage()->getAmount());
            self::assertSame($this->sphere->getRump()->getTorpedoLevel(), $storage->getTorpedo()->getLevel());
            self::assertTrue($this->sphere->getTorpedoState());
            if (!$empty && $hour === 1) {
                self::assertSame($type, $storage->getTorpedo());
            }
        }
    }

    public function testFirstHourlyRegenerationUsesIndependentRandomValuesAndReactivatesSphere(): void
    {
        $container = $this->getContainer();
        if (!$this->sphere->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)) {
            $system = new SpacecraftSystem()->setSpacecraft($this->sphere)->setSystemType(SpacecraftSystemTypeEnum::SHIELDS)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            $this->sphere->getSystems()->set(SpacecraftSystemTypeEnum::SHIELDS->value, $system);
            $this->em->persist($system);
        }
        $maxHull = $this->sphere->getMaxHull();
        $maxShield = $this->sphere->getMaxShield(true);
        $hull = (int) ceil($maxHull / 10);
        $shield = (int) ceil($maxShield / 10);
        $this->sphere->getCondition()->setHull($hull)->setShield($shield);
        $rolls = [2, 3];
        $expectedStatuses = [];
        foreach ($this->sphere->getSystems() as $system) {
            $system->setStatus(10);
            if ($system->getSystemType() === SpacecraftSystemTypeEnum::TRANSWARP_COIL) {
                $expectedStatuses[$system->getSystemType()->value] = 11;
                continue;
            }
            $roll = 2 + count($rolls) % 9;
            $rolls[] = $roll;
            $expectedStatuses[$system->getSystemType()->value] = 10 + $roll;
        }
        $this->event->setNextTickAt($this->now);
        $this->em->flush();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->with(2, 10)->times(count($rolls))->andReturn(...$rolls);
        $regeneration = $container->make(BorgSphereRegeneration::class, ['random' => $random]);
        $tick = $container->make(BorgSphereTick::class, ['regeneration' => $regeneration]);

        $this->em->wrapInTransaction(fn () => $tick->work());

        self::assertFalse($this->sphere->getCondition()->isDisabled());
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $this->event->getPhase());
        self::assertSame($hull + (int) ceil($maxHull * 2 / 100), $this->sphere->getCondition()->getHull());
        self::assertSame($shield + (int) ceil($maxShield * 3 / 100), $this->sphere->getCondition()->getShield());
        foreach ($expectedStatuses as $type => $expected) {
            self::assertSame($expected, $this->sphere->getSpacecraftSystem(SpacecraftSystemTypeEnum::from($type))->getStatus());
        }
        self::assertSame(1100, $this->event->getTranswarpProgress());
        self::assertSame(1000, $this->event->getTranswarpFloor());
        self::assertSame($this->now + 3600, $this->event->getNextTickAt());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $this->sphere->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getMode());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $this->sphere->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getMode());
        $this->source->setLocation($this->sphere->getLocation());
        $this->sphere->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->setStatus(12);
        $this->sphere->getCondition()->setShield((int) ceil($maxShield / 5));
        $this->em->flush();
        self::assertStringContainsString(sprintf('<tr><th>Schilde</th><td>%d / %d</td></tr>', $this->sphere->getCondition()->getShield(), $maxShield), $this->renderPopup());
    }

    /** @return iterable<string, array{bool}> */
    public static function disablingWeaponPhases(): iterable
    {
        yield 'energy weapon disables sphere' => [false];
        yield 'torpedo disables sphere' => [true];
    }

    #[DataProvider('disablingWeaponPhases')]
    public function testDisablingHitStopsAllRemainingSalvosAndCounterfire(bool $torpedo): void
    {
        $container = $this->getContainer();
        $context = new BorgSphereAttackContext();
        $combat = $container->make(BorgSphereCombat::class, ['context' => $context]);
        $source = $this->combatWrapper($this->source, 100);
        $sphere = $this->combatWrapper($this->sphere, 10);
        $escortShip = $this->em->find(Ship::class, 78)->setLocation($this->sphere->getLocation());
        $escort = $this->combatWrapper($escortShip, 100);
        $this->source->getRump()->setPhaserVolleys($torpedo ? 0 : 4)->setTorpedoVolleys(4);
        $weapon = $this->source->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getModule()->getWeapon();
        $weapon->setCriticalChance(0)->setVariance(0)->setFiringMode(EnergyWeaponPhase::FIRINGMODE_FOCUS);
        $this->sphere->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->setStatus(11);
        $this->sphere->getCondition()->setHull((int) ceil($this->sphere->getMaxHull() / 10) + 1)->setShield(0)->setDisabled(false);
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING);
        $type = $this->em->getRepository(TorpedoType::class)->findOneBy(['level' => $this->source->getRump()->getTorpedoLevel()]);
        self::assertNotNull($type);
        $container->get(ShipTorpedoManagerInterface::class)->changeTorpedo($source, 10, $type);
        $beforeTorpedoes = $this->source->getTorpedoCount();
        self::assertGreaterThan(0, $beforeTorpedoes);
        self::assertTrue($sphere->canFire());
        self::assertTrue($escort->canFire());
        self::assertTrue($source->get()->getTorpedoState());
        $this->em->flush();
        $parties = $container->get(BattlePartyFactoryInterface::class);
        $attacker = $parties->createSingletonBattleParty($source);
        $defenders = $parties->createMixedBattleParty(new ArrayCollection([$this->sphere->getId() => $sphere, $escortShip->getId() => $escort]));
        $cycle = $this->combatCycle($context, $combat);

        $messages = $this->em->wrapInTransaction(fn () => $cycle->cycle($attacker, $defenders, SpacecraftAttackCauseEnum::SHIP_FIGHT));

        self::assertTrue($this->sphere->getCondition()->isDisabled(), implode("\n", $messages->getInformationDump()->getInformations()));
        self::assertSame(BorgSpherePhaseEnum::REGENERATING, $this->event->getPhase());
        self::assertSame(99, $source->getEpsSystemData()->getEps());
        self::assertSame($beforeTorpedoes - ($torpedo ? 1 : 0), $this->source->getTorpedoCount());
        self::assertSame(100, $sphere->getEpsSystemData()->getEps());
        self::assertSame(100, $escort->getEpsSystemData()->getEps());
        self::assertFalse($defenders->isDefeated());
        self::assertFalse($context->isBattleStopped());
        $report = implode("\n", $messages->getInformationDump()->getInformations());
        self::assertSame(1, substr_count($report, ' feuert '), $report);
        self::assertStringContainsString('Die Sphäre wurde deaktiviert. Der Beschuss wurde eingestellt', $report);
    }

    private function combatWrapper(Ship $ship, int $status): SpacecraftWrapperInterface
    {
        $ship->setFleet(null)->setDockedTo(null);
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::TORPEDO] as $type) {
            if (!$ship->hasSpacecraftSystem($type)) {
                $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type);
                if ($type === SpacecraftSystemTypeEnum::TORPEDO) {
                    $module = $this->em->getRepository(Module::class)->findOneBy(['type' => SpacecraftModuleTypeEnum::TORPEDO]);
                    self::assertNotNull($module);
                    $system->setModule($module)->setData('{"shieldPenetration":0}');
                }
                $ship->getSystems()->set($type->value, $system);
                $this->em->persist($system);
            }
        }
        foreach ($ship->getSystems() as $system) {
            $system->setStatus($status);
            if (in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO], true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_ON);
            } elseif (in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::WARPDRIVE, SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::CLOAK], true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
        }
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($ship);
        $wrapper->getEpsSystemData()->setMaxEps(1000)->setEps(100)->update();
        $wrapper->getComputerSystemDataMandatory()->setHitChance(100)->setEvadeChance(0)->update();
        $wrapper->getEnergyWeaponSystemData()->setBaseDamage(50)->update();
        return $wrapper;
    }

    public function testActiveSphereWithAnOldRegeneratingPhaseTakesAndDealsDamage(): void
    {
        $container = $this->getContainer();
        $source = $this->combatWrapper($this->source, 100);
        $sphere = $this->combatWrapper($this->sphere, 100);
        foreach ([$this->source, $this->sphere] as $ship) {
            $ship->setMaxHull(10000);
            $ship->getCondition()->setHull(10000)->setShield(0)->setDisabled(false);
            $ship->getRump()->setPhaserVolleys(2)->setTorpedoVolleys(0);
            $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getModule()->getWeapon()
                ->setCriticalChance(0)->setVariance(0)->setFiringMode(EnergyWeaponPhase::FIRINGMODE_FOCUS);
        }
        $this->event->setPhase(BorgSpherePhaseEnum::REGENERATING)->setTranswarpProgress(1200);
        $deadline = $this->event->getNextTickAt();
        $this->em->flush();
        $context = new BorgSphereAttackContext();
        $combat = $container->make(BorgSphereCombat::class, ['context' => $context]);
        $parties = $container->get(BattlePartyFactoryInterface::class);
        $attackers = $parties->createSingletonBattleParty($sphere);
        $defenders = $parties->createSingletonBattleParty($source);
        $cycle = $this->combatCycle($context, $combat);

        $messages = $this->em->wrapInTransaction(fn () => $cycle->cycle($attackers, $defenders, SpacecraftAttackCauseEnum::SHIP_FIGHT));

        $report = implode("\n", $messages->getInformationDump()->getInformations());
        self::assertLessThan(10000, $this->source->getCondition()->getHull(), $report);
        self::assertLessThan(10000, $this->sphere->getCondition()->getHull(), $report);
        self::assertSame(4, substr_count($report, ' feuert '), $report);
        self::assertSame(4, substr_count($report, '- Hüllenschaden:'), $report);
        $this->em->refresh($this->event);
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $this->event->getPhase());
        self::assertSame($deadline, $this->event->getNextTickAt());
        self::assertSame(1200, $this->event->getTranswarpProgress());
        self::assertSame(1000, $this->event->getTranswarpFloor());
    }

    public function testProtectedSphereHitExplainsWhyDamageWasStopped(): void
    {
        $container = $this->getContainer();
        $context = new BorgSphereAttackContext();
        $combat = $container->make(BorgSphereCombat::class, ['context' => $context]);
        $damage = $container->make(ApplyDamage::class, ['borgSphereCombat' => $combat]);
        $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->sphere);
        $information = new InformationWrapper();
        $hull = $this->sphere->getCondition()->getHull();
        $shield = $this->sphere->getCondition()->getShield();

        $this->em->wrapInTransaction(fn () => $damage->damage(new DamageWrapper(50), $wrapper, $information));

        self::assertSame(['-- Die Sphäre befindet sich im Regenerationsmodus. Der Beschuss wurde eingestellt'], $information->getInformations());
        self::assertSame($hull, $this->sphere->getCondition()->getHull());
        self::assertSame($shield, $this->sphere->getCondition()->getShield());
        self::assertTrue($context->isBattleStopped());
    }

    private function combatCycle(BorgSphereAttackContext $context, BorgSphereCombat $combat): SpacecraftAttackCycle
    {
        $container = $this->getContainer();
        $systemDamage = $container->make(SystemDamage::class, ['borgSphereCombat' => $combat]);
        $damage = $container->make(ApplyDamage::class, ['borgSphereCombat' => $combat, 'systemDamage' => $systemDamage]);
        $preparation = $this->mock(SpacecraftAttackPreparationInterface::class);
        $preparation->shouldReceive('getReady')->once();
        return $container->make(SpacecraftAttackCycle::class, [
            'borgSphereAttackContext' => $context,
            'energyWeaponPhase' => $container->make(EnergyWeaponPhase::class, ['applyDamage' => $damage, 'borgSphereAttackContext' => $context]),
            'projectileWeaponPhase' => $container->make(ProjectileWeaponPhase::class, ['applyDamage' => $damage, 'borgSphereAttackContext' => $context]),
            'spacecraftAttackPreparation' => $preparation
        ]);
    }

    public function testBorgPopupsAndActionsUseSharedControllersForShipsAndStations(): void
    {
        $container = $this->getContainer();
        $discovery = $container->get(ControllerDiscoveryInterface::class);
        $sharedActions = $container->get('SPACECRAFT_ACTIONS');
        $sharedViews = $container->get('SPACECRAFT_VIEWS');
        foreach ([ModuleEnum::SHIP, ModuleEnum::STATION] as $module) {
            $actions = $discovery->getControllers($module, false);
            $views = $discovery->getControllers($module, true);
            foreach ([AttackSpacecraft::ACTION_IDENTIFIER, CollectBorgNanites::ACTION_IDENTIFIER] as $identifier) {
                self::assertSame($sharedActions[$identifier], $actions[$identifier], $module->name . ': ' . $identifier);
            }
            self::assertSame($sharedViews[ShowBorgSphere::VIEW_IDENTIFIER], $views[ShowBorgSphere::VIEW_IDENTIFIER], $module->name);
        }
    }

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->events = $container->get(BorgSphereRepositoryInterface::class);
        $this->now = $container->get(StuTime::class)->time();
        $this->source = $this->em->find(Ship::class, 42);
        $this->sphere = $this->em->find(Ship::class, 77);
        for ($i = 0; $i < 5; $i++) {
            $assignment = $container->get(CrewCreatorInterface::class)->create($this->source->getUser()->getId())->setSpacecraft($this->source)->setSlot(CrewTypeEnum::CREWMAN);
            $this->source->getCrewAssignments()->add($assignment);
            $this->em->persist($assignment);
        }
        $colony = $this->em->find(Colony::class, 42);
        $this->source->setLocation($colony->getStarsystemMap());
        $this->sphere->setLocation($colony->getStarsystemMap())->setUser($this->em->find(User::class, 10));
        foreach ($this->source->getSystems() as $system) {
            if (in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::WARPDRIVE, SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::CLOAK], true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
        }
        $quest = new NPCQuest()->setUser($this->em->find(User::class, 10))->setUserId(10)->setTitle('Sphärentest')->setStart($this->now - 100);
        $this->em->persist($quest);
        $this->configuration = new BorgSphereConfiguration()->setId(1)->setColony($colony)->setQuest($quest)->setUser($this->em->find(User::class, 10))->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))->setEndTime($this->now + 604800)->setSettings($this->settings());
        $this->event = new BorgSphereEvent()->setConfiguration($this->configuration)->setShipId($this->sphere->getId())->setPhase(BorgSpherePhaseEnum::REGENERATING)->setFirstDisabledAt($this->now)->setTranswarpProgress(1000)->setNextTickAt($this->now + 3600);
        $this->sphere->getCondition()->setDisabled(true);
        $this->em->persist($this->configuration);
        $this->em->persist($this->event);
        $this->em->flush();
    }

    public function testRaidAddsNanitesAndPersistsAccountCooldownAcrossShips(): void
    {
        $service = $this->raidService(success: true);
        $before = $this->source->getStorage()->get(2)?->getAmount() ?? 0;
        $this->collect($service);
        self::assertSame($before + 5, $this->source->getStorage()->get(2)->getAmount());
        $raid = $this->events->getRaid($this->source->getUser());
        self::assertSame($this->now, $raid->getLastAttemptAt());
        self::assertSame(1, $raid->getAttempts());
        $other = $this->em->find(Ship::class, 78);
        $other->setLocation($this->sphere->getLocation());
        $information = $this->collect($service, $other);
        self::assertStringContainsString('Ein weiteres Außenteam kann ab', $information->getInformations()[0]);
        self::assertSame(1, $raid->getAttempts());
    }

    public function testFailedRaidPermanentlyRemovesCrewAndAllowsAnImmediateRetry(): void
    {
        $captain = $this->source->getCrewAssignments()->first();
        $captain->setSlot(CrewTypeEnum::CAPTAIN);
        $before = $this->source->getCrewCount();
        $crewCount = $this->em->getRepository(Crew::class)->count([]);
        $information = $this->collect($this->raidService(success: false));
        self::assertSame($before - 2, $this->source->getCrewCount());
        self::assertSame($crewCount - 2, $this->em->getRepository(Crew::class)->count([]));
        self::assertTrue($this->source->getCrewAssignments()->contains($captain));
        self::assertStringContainsString('2 Crewmitglieder', $information->getInformations()[0]);
        self::assertStringContainsString('assimiliert', $information->getInformations()[0]);
        $raid = $this->events->getRaid($this->source->getUser());
        self::assertSame(0, $raid->getLastAttemptAt());
        self::assertSame(1, $raid->getAttempts());
        self::assertSame(0, $this->raidService(success: true, attempts: 0)->getCooldownUntil($this->source->getUser(), $this->event));
        $this->collect($this->raidService(success: true));
        self::assertSame(2, $raid->getAttempts());
        self::assertSame($this->now, $raid->getLastAttemptAt());
    }

    /** @return iterable<string, array{BorgSpherePhaseEnum, int, bool}> */
    public static function raidSuccessBoundaries(): iterable
    {
        yield 'active sphere roll 90 succeeds' => [BorgSpherePhaseEnum::ROAMING, 90, true];
        yield 'active sphere roll 91 fails' => [BorgSpherePhaseEnum::ROAMING, 91, false];
        yield 'regenerating sphere roll 90 succeeds' => [BorgSpherePhaseEnum::REGENERATING, 90, true];
        yield 'regenerating sphere roll 91 fails' => [BorgSpherePhaseEnum::REGENERATING, 91, false];
    }

    #[DataProvider('raidSuccessBoundaries')]
    public function testRaidSuccessChanceIsNinetyPercentForBothPhases(BorgSpherePhaseEnum $phase, int $roll, bool $success): void
    {
        $this->event->setPhase($phase);
        $this->sphere->getCondition()->setDisabled($phase === BorgSpherePhaseEnum::REGENERATING);
        $this->em->flush();
        $information = $this->collect($this->raidService($success, roll: $roll));
        $raid = $this->events->getRaid($this->source->getUser());
        self::assertSame(1, $raid->getAttempts());
        self::assertSame($success ? $this->now : 0, $raid->getLastAttemptAt());
        self::assertStringContainsString($success ? 'wurden dem Lagerraum' : 'ist gescheitert', $information->getInformations()[0]);
    }

    public function testFailedRaidKeepsThePreviousExpiredSuccessTimestamp(): void
    {
        $previousSuccess = $this->now - 7201;
        $raid = new BorgSphereRaid()->setUser($this->source->getUser())->setLastAttemptAt($previousSuccess)->setAttempts(3);
        $this->events->saveRaid($raid);
        $this->em->flush();
        $service = $this->raidService(success: false);
        $this->collect($service);
        self::assertSame($previousSuccess, $raid->getLastAttemptAt());
        self::assertSame(4, $raid->getAttempts());
        self::assertSame($this->now - 1, $service->getCooldownUntil($this->source->getUser(), $this->event));
    }

    public function testFullStorageRejectsRaidWithoutCooldownOrCrewLoss(): void
    {
        $remaining = $this->source->getMaxStorage() - $this->source->getStorageSum();
        $this->getContainer()->get(StorageManagerInterface::class)->upperStorage($this->source, $this->em->find(Commodity::class, 1), $remaining);
        $this->em->flush();
        $before = $this->source->getCrewCount();
        $information = $this->collect($this->raidService(success: true, attempts: 0));
        self::assertStringContainsString('5 freie Lagerplätze', $information->getInformations()[0]);
        self::assertNull($this->events->getRaid($this->source->getUser()));
        self::assertSame($before, $this->source->getCrewCount());
    }

    public function testCooldownExpiresAfterExactlyTwoHours(): void
    {
        $service = $this->raidService(success: true, attempts: 2);
        $this->collect($service);
        $raid = $this->events->getRaid($this->source->getUser());
        $raid->setLastAttemptAt($this->now - 7200);
        $this->em->flush();
        $this->collect($service);
        self::assertSame(2, $raid->getAttempts());
    }

    public function testCompletedEventCannotBeFarmed(): void
    {
        $this->configuration->setEndTime($this->now);
        $information = $this->collect($this->raidService(success: true, attempts: 0));
        self::assertStringContainsString('nicht mehr für eine Außenmission verfügbar', $information->getInformations()[0]);
        self::assertNull($this->events->getRaid($this->source->getUser()));
    }

    public function testSpawnIsAwayFromColonyUsesInstanceStatsAndCannotBeRepeated(): void
    {
        $this->events->deleteConfiguration($this->configuration);
        $this->em->flush();
        $this->em->clear();
        $this->configuration = new BorgSphereConfiguration()->setId(2)->setColony($this->em->find(Colony::class, 42))->setQuest($this->em->find(NPCQuest::class, $this->configuration->getQuest()->getId()))->setUser($this->em->find(User::class, 10))->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))->setEndTime($this->now + 604800)->setSettings($this->settings());
        $this->em->persist($this->configuration);
        $this->em->flush();
        $spawner = $this->getContainer()->get(BorgSphereSpawner::class);
        $event = $this->em->wrapInTransaction(fn () => $spawner->spawn(2, new InformationWrapper()));
        self::assertNotNull($event);
        $sphere = $this->em->find(Ship::class, $event->getShipId());
        self::assertNotSame($this->configuration->getColony()->getStarsystemMap(), $sphere->getLocation());
        self::assertSame($this->configuration->getColony()->getSystem(), $sphere->getSystem());
        self::assertSame($this->source->getMaxHull(), $sphere->getMaxHull());
        self::assertSame($this->source->getMaxShield(true), $sphere->getMaxShield(true));
        self::assertSame($sphere->getMaxShield(true), $sphere->getCondition()->getShield());
        self::assertTrue($sphere->isShielded());
        self::assertTrue($sphere->hasActiveWeapon());
        self::assertTrue($sphere->getNbs());
        self::assertSame(SpacecraftAlertStateEnum::ALERT_YELLOW, $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($sphere)->getAlertState());
        self::assertNull($this->em->wrapInTransaction(fn () => $spawner->spawn(2, new InformationWrapper())));
    }

    public function testNavigationRefusesRedAlertAndApproachesViaSafeFields(): void
    {
        $report = [];
        $messages = $this->mock(DistributedMessageSenderInterface::class);
        $messages->shouldReceive('distributeMessageCollection')->andReturnUsing(function (MessageCollectionInterface $collection) use (&$report): void {
            $report = $collection->getInformationDump()->getInformations();
        });
        $flight = $this->getContainer()->make(BorgSphereFlight::class, ['messages' => $messages]);
        $navigation = $this->getContainer()->make(BorgSphereNavigation::class, ['flight' => $flight]);
        $spawn = $navigation->spawnLocation($this->configuration);
        self::assertNotNull($spawn);
        $this->source->getLocation()->getSpacecrafts()->removeElement($this->source);
        $this->source->getFleet()?->getShips()->toArray();
        $this->source->setLocation($spawn)->setDockedTo(null)->setFleet(null);
        $spawn->getSpacecrafts()->set($this->source->getId(), $this->source);
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->sphere);
        $wrapper->setAlertState(SpacecraftAlertStateEnum::ALERT_RED);
        self::assertFalse($navigation->isSafe($this->sphere->getLocation()));
        $this->em->flush();
        self::assertNull($this->source->getFleet());
        $navigation->approach($this->source, $this->configuration);
        self::assertNotSame($spawn, $this->source->getLocation(), implode(' ', $report));
        self::assertNotSame($this->sphere->getLocation(), $this->source->getLocation());
        self::assertInstanceOf(StarSystemMap::class, $this->source->getLocation());
        self::assertTrue($navigation->isSafe($this->source->getLocation()));
        self::assertFalse($this->source->getWarpDriveState());
    }

    /** @return iterable<string, array{int}> */
    public static function movementBudgets(): iterable
    {
        yield 'one field' => [1];
        yield 'seven fields' => [7];
    }

    #[DataProvider('movementBudgets')]
    public function testTheNormalFlightPipelineCanUseTheWholeRandomMovementBudget(int $budget): void
    {
        $container = $this->getContainer();
        $ship = $this->source;
        $systemMaps = $container->get(StarSystemMapRepositoryInterface::class);
        $fieldType = $this->em->find(MapFieldType::class, 3112);
        $fields = [];
        for ($x = 2; $x <= 9; $x++) {
            $field = $systemMaps->getByCoordinates($this->configuration->getColony()->getSystem()->getId(), $x, 2);
            self::assertNotNull($field);
            $field->setFieldType($fieldType);
            $field->getSpacecrafts()->clear();
            $fields[$x] = $field;
        }
        $ship->setLocation($fields[2]);
        $ship->getCondition()->setDisabled(false);
        $ship->getBuildplan()->setCrew($ship->getCrewCount());
        $ship->getFleet()?->getShips()->toArray();
        $wrapper = $this->combatWrapper($ship, 100);
        $wrapper->getEpsSystemData()->setEps(1000)->update();
        $wrapper->getComputerSystemDataMandatory()->setAlertState(SpacecraftAlertStateEnum::ALERT_YELLOW)->update();
        $this->em->flush();
        self::assertNull($ship->getFleet());
        self::assertSame($ship, $wrapper->get());
        self::assertSame($fields[2], $ship->getLocation());
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->with(1, 7)->once()->andReturn($budget);
        $random->shouldReceive('array_rand')->times($budget)->andReturnUsing(static fn (array $candidates): int => array_key_first($candidates));
        $movedTo = [];
        $report = [];
        $messages = $this->mock(DistributedMessageSenderInterface::class);
        $messages->shouldReceive('distributeMessageCollection')->times($budget)->andReturnUsing(static function (MessageCollectionInterface $messages) use (&$movedTo, &$report, $ship): void {
            $movedTo[] = $ship->getLocation()->getId();
            $report = [...$report, ...$messages->getInformationDump()->getInformations()];
        });
        $flight = $container->make(BorgSphereFlight::class, ['messages' => $messages]);
        $navigation = $container->make(BorgSphereNavigation::class, ['flight' => $flight, 'random' => $random]);

        $this->em->wrapInTransaction(fn () => $navigation->roam($ship, $this->configuration));

        self::assertSame($fields[2 + $budget], $ship->getLocation(), implode("\n", $report));
        self::assertCount($budget, array_unique($movedTo));
        self::assertFalse($ship->getWarpDriveState());
    }

    /** @return iterable<string, array{AnomalyTypeEnum, BorgSpherePhaseEnum}> */
    public static function anomalyEscapeStates(): iterable
    {
        foreach ([AnomalyTypeEnum::SUBSPACE_ELLIPSE, AnomalyTypeEnum::ION_STORM] as $type) {
            foreach ([BorgSpherePhaseEnum::APPROACH, BorgSpherePhaseEnum::ROAMING] as $phase) {
                yield $type->name . ' ' . $phase->name => [$type, $phase];
            }
        }
    }

    #[DataProvider('anomalyEscapeStates')]
    public function testTickEscapesAnAnomalyBeforeAttackingOrChoosingToStay(AnomalyTypeEnum $type, BorgSpherePhaseEnum $phase): void
    {
        $container = $this->getContainer();
        $systemMaps = $container->get(StarSystemMapRepositoryInterface::class);
        $systemId = $this->configuration->getColony()->getSystem()->getId();
        $start = $systemMaps->getByCoordinates($systemId, 2, 2);
        $destination = $systemMaps->getByCoordinates($systemId, 3, 2);
        self::assertNotNull($start);
        self::assertNotNull($destination);
        foreach ([$start, $destination] as $field) {
            $field->setFieldType($this->em->find(MapFieldType::class, 3112));
            $field->getSpacecrafts()->clear();
        }
        $this->sphere->getFleet()?->getShips()->toArray();
        $wrapper = $this->combatWrapper($this->sphere, 100);
        foreach ([SpacecraftSystemTypeEnum::IMPULSEDRIVE, SpacecraftSystemTypeEnum::DEFLECTOR] as $typeToAdd) {
            if (!$this->sphere->hasSpacecraftSystem($typeToAdd)) {
                $system = new SpacecraftSystem()->setSpacecraft($this->sphere)->setSystemType($typeToAdd)
                    ->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON);
                $this->sphere->getSystems()->set($typeToAdd->value, $system);
                $this->em->persist($system);
            }
        }
        $this->sphere->setLocation($start);
        $this->source->setLocation($start);
        $this->sphere->getBuildplan()->setCrew($this->sphere->getCrewCount());
        $this->sphere->getCondition()->setDisabled(false);
        $start->getSpacecrafts()->set($this->sphere->getId(), $this->sphere);
        $start->getSpacecrafts()->set($this->source->getId(), $this->source);
        $wrapper->setAlertState(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $anomaly = new Anomaly()->setAnomalyType($this->em->find(AnomalyType::class, $type->value))->setRemainingTicks(2)->setLocation($start);
        $this->em->persist($anomaly);
        $this->event->setPhase($phase)->setFirstDisabledAt(null)->setNextTickAt($this->now);
        $this->em->flush();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 7)->andReturn(1);
        $navigation = $container->make(BorgSphereNavigation::class, ['random' => $random]);
        $idle = $this->mock(StuRandom::class);
        $idle->shouldNotReceive('rand');
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldNotReceive('attack');
        $battle = $container->make(BorgSphereBattle::class, ['attacks' => $attacks]);
        $behavior = $container->make(BorgSphereBehavior::class, ['navigation' => $navigation, 'battle' => $battle, 'random' => $idle]);
        $tick = $container->make(BorgSphereTick::class, ['behavior' => $behavior]);

        $this->em->wrapInTransaction(fn () => $tick->work());

        self::assertSame($destination, $this->sphere->getLocation());
        self::assertTrue($destination->getAnomalies()->isEmpty());
        self::assertFalse($this->sphere->getWarpDriveState());
        self::assertSame($phase, $this->event->getPhase());
    }

    /** @return iterable<string, array{bool, bool}> */
    public static function interceptionGroups(): iterable
    {
        yield 'one single ship' => [false, true];
        yield 'one fleet with a remote warped member' => [true, true];
        yield 'one fleet with a remote unwarped member' => [true, false];
    }

    #[DataProvider('interceptionGroups')]
    public function testIdleTickInterceptsAndAttacksOnlyOneLocalGroup(bool $fleetTarget, bool $remoteWarped): void
    {
        $container = $this->getContainer();
        $location = $this->configuration->getColony()->getSystem()->getMap();
        self::assertNotNull($location);
        foreach ($location->getAnomalies() as $anomaly) {
            $anomaly->setRemainingTicks(0);
        }
        $location->getSpacecrafts()->clear();
        $this->sphere->getFleet()?->getShips()->toArray();
        $source = $this->combatWrapper($this->sphere, 100);
        $this->sphere->getCondition()->setDisabled(false);
        $this->sphere->setLocation($location)->setTractoredShip(null);
        $this->sphere->setTractoringSpacecraft(null);
        $this->sphere->getBuildplan()->setCrew($this->sphere->getCrewCount());
        $location->getSpacecrafts()->set($this->sphere->getId(), $this->sphere);
        $targets = [$this->source, $this->em->find(Ship::class, 78), $this->em->find(Ship::class, 79)];
        foreach ($targets as $target) {
            $target->getFleet()?->getShips()->toArray();
            $this->combatWrapper($target, 100);
            $target->setUser($this->source->getUser())->setLocation($location);
            $target->getCondition()->setDisabled(false)->setIsDestroyed(false);
            $target->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            $location->getSpacecrafts()->set($target->getId(), $target);
        }
        if ($fleetTarget) {
            $fleet = $this->em->find(Fleet::class, 42);
            $fleet->getShips()->clear();
            $fleet->setName('Abfangflotte');
            foreach ($targets as $target) {
                $target->setFleet($fleet);
            }
            $targets[2]->setLocation($this->configuration->getColony()->getStarsystemMap());
            $targets[2]->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->setMode($remoteWarped ? SpacecraftSystemModeEnum::MODE_ON : SpacecraftSystemModeEnum::MODE_OFF);
            $location->getSpacecrafts()->removeElement($targets[2]);
        }
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING)->setFirstDisabledAt(null)->setNextTickAt($this->now);
        $this->em->flush();
        self::assertTrue($this->sphere->canIntercept());
        $attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $attacks->shouldReceive('attack')->once()->andReturnUsing(function (SpacecraftWrapperInterface $attacker, SpacecraftWrapperInterface $target) use ($targets): bool {
            self::assertSame($this->sphere, $attacker->get());
            self::assertSame($targets[0], $target->get());
            self::assertFalse($target->get()->isWarped());
            return true;
        });
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $battle = $container->make(BorgSphereBattle::class, ['attacks' => $attacks, 'relations' => $relations]);
        $parties = $container->get(BattlePartyFactoryInterface::class);
        $alerted = $parties->createAlertStateBattleParty($source);
        $detection = $this->mock(AlertDetectionInterface::class);
        $detection->shouldReceive('detectAlertedBattleParties')->once()->andReturn([$alerted]);
        $alertCycle = $this->mock(SpacecraftAttackCycleInterface::class);
        $alertCycle->shouldReceive('cycle')->once()->andReturnUsing(function ($attackers, IncomingBattleParty $incoming) use ($location, $fleetTarget, $container): MessageCollectionInterface {
            $members = $incoming->getActiveMembers();
            self::assertCount($fleetTarget ? 2 : 1, $members);
            foreach ($members as $member) {
                self::assertSame($location, $member->get()->getLocation());
            }
            return $container->get(MessageFactoryInterface::class)->createMessageCollection();
        });
        $alerts = $container->make(AlertReactionFacade::class, ['alertDetection' => $detection, 'spacecraftAttackCycle' => $alertCycle]);
        $core = $container->make(InterceptShipCore::class, ['alertReactionFacade' => $alerts]);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(40);
        $random->shouldReceive('array_rand')->once()->andReturnUsing(static fn (array $groups): string => array_key_first($groups));
        $interception = $container->make(BorgSphereInterception::class, ['interception' => $core, 'battle' => $battle, 'relations' => $relations, 'random' => $random]);
        $idle = $this->mock(StuRandom::class);
        $idle->shouldReceive('rand')->once()->with(1, 100)->andReturn(1);
        $behavior = $container->make(BorgSphereBehavior::class, ['battle' => $battle, 'interception' => $interception, 'random' => $idle]);
        $tick = $container->make(BorgSphereTick::class, ['behavior' => $behavior]);

        $this->em->wrapInTransaction(fn () => $tick->work());

        self::assertSame($location, $this->sphere->getLocation());
        self::assertFalse($targets[0]->getWarpDriveState());
        self::assertSame(!$fleetTarget, $targets[1]->getWarpDriveState());
        self::assertSame($remoteWarped, $targets[2]->getWarpDriveState());
        foreach ($targets as $target) {
            $system = $target->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE);
            $expected = $system->getMode();
            $this->em->refresh($system);
            self::assertSame($expected, $system->getMode());
        }
    }

    public function testSpawnSkipsEvenTheFarthestSectorWhenRedAlertShipsArePresent(): void
    {
        $navigation = $this->getContainer()->get(BorgSphereNavigation::class);
        $first = $navigation->spawnLocation($this->configuration);
        self::assertNotNull($first);
        $this->source->getLocation()->getSpacecrafts()->removeElement($this->source);
        $this->source->setLocation($first);
        $first->getSpacecrafts()->set($this->source->getId(), $this->source);
        $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->source)->setAlertState(SpacecraftAlertStateEnum::ALERT_RED);
        $this->em->flush();
        $safe = $navigation->spawnLocation($this->configuration);
        self::assertNotNull($safe);
        self::assertNotSame($first, $safe);
        self::assertTrue($navigation->isSafe($safe));
    }

    public function testDeletingQuestRetiresSphereAndRemovesEventConfiguration(): void
    {
        $id = $this->sphere->getId();
        $this->em->remove($this->configuration->getQuest());
        $this->em->flush();
        self::assertNull($this->em->find(Ship::class, $id));
        self::assertSame(0, $this->em->getRepository(BorgSphereConfiguration::class)->count([]));
        self::assertSame(0, $this->em->getRepository(BorgSphereEvent::class)->count([]));
    }

    public function testExpiredSphereRetreatsInsteadOfRemainingAsFarm(): void
    {
        $id = $this->sphere->getId();
        $this->configuration->setEndTime($this->now);
        $this->event->setNextTickAt($this->now);
        $this->em->flush();
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(BorgSphereTick::class)->work());
        self::assertSame(BorgSpherePhaseEnum::ESCAPED, $this->event->getPhase());
        self::assertNull($this->configuration->getQuest()->getEnd());
        self::assertNull($this->event->getShipId());
        self::assertNull($this->em->find(Ship::class, $id));
    }

    public function testPopupRendersWorkingControlsAndThenAccountCooldown(): void
    {
        $this->sphere->setName('[b]Test-Sphäre[/b]');
        $html = $this->renderPopup();
        self::assertStringContainsString('<th id="popup-drag"><strong>Test-Sphäre</strong></th>', $html);
        self::assertSame(1, substr_count($html, 'Test-Sphäre'));
        self::assertStringNotContainsString('Das Außenteam umfasst', $html);
        self::assertStringContainsString('<tr><th>Möglicher Gewinn</th><td>5 Naniten</td></tr>', $html);
        self::assertStringContainsString('Außenteam senden', $html);
        self::assertStringContainsString('class="button"', $html);
        self::assertStringContainsString('Transwarpantrieb', $html);
        self::assertStringNotContainsString('Transwarpantrieb beschießen', $html);
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING);
        $this->sphere->getCondition()->setDisabled(false);
        $html = $this->renderPopup();
        self::assertStringContainsString('Transwarpantrieb beschießen', $html);
        $this->events->saveRaid(new BorgSphereRaid()->setUser($this->source->getUser())->setLastAttemptAt($this->now)->setAttempts(1));
        $this->em->flush();
        $html = $this->renderPopup();
        self::assertStringNotContainsString('Außenteam senden', $html);
        self::assertStringContainsString('Nächste Außenmission ab', $html);
        self::assertStringContainsString('Uhr', $html);
    }

    /** @return iterable<string, array{int}> */
    public static function missionSurplus(): iterable
    {
        yield 'buildplan minimum' => [0];
        yield 'minimum plus two' => [2];
        yield 'minimum plus five' => [5];
    }

    #[DataProvider('missionSurplus')]
    public function testPopupAndMissionUseOnlyCrewAboveTheBuildplanMinimum(int $surplus): void
    {
        $minimum = $this->source->getCrewCount() - $surplus;
        $this->source->getBuildplan()->setCrew($minimum);
        $service = $this->raidService(success: false, attempts: $surplus === 0 ? 0 : 1);
        self::assertSame($surplus, $service->getTeamSize($this->source, $this->event));
        $this->em->flush();
        $html = $this->renderPopup();
        if ($surplus === 0) {
            self::assertStringNotContainsString('Außenteam senden', $html);
            $information = $this->collect($service);
            self::assertCount(1, $information->getInformations());
            self::assertStringContainsString('Mindestbesatzung des Bauplans (' . $minimum . ')', $information->getInformations()[0]);
            self::assertNull($this->events->getRaid($this->source->getUser()));
        } else {
            self::assertStringContainsString('Außenteam senden', $html);
            $this->collect($service);
        }
        self::assertGreaterThanOrEqual($minimum, $this->source->getCrewCount());
    }

    public function testTranswarpEscapeLeavesTheQuestOpen(): void
    {
        $this->event->setTranswarpProgress(7900)->setNextTickAt($this->now);
        $this->em->flush();
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(BorgSphereTick::class)->work());
        self::assertSame(BorgSpherePhaseEnum::ESCAPED, $this->event->getPhase());
        self::assertNull($this->configuration->getQuest()->getEnd());
    }

    private function renderPopup(): string
    {
        $variables = ['SESSIONSTRING' => 'session', 'INFORMATION' => [], 'ACHIEVEMENTS' => [], 'EXECUTEJSAFTERRENDER' => [], 'JAVASCRIPTPATH' => '/static', 'MACRO' => 'html/ship/borgSphere.twig'];
        $game = $this->mock(ViewControllerContext::class);
        $game->shouldReceive('getUser')->andReturn($this->source->getUser());
        $game->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $game->shouldReceive('setPageTitle')->once()->with($this->sphere->getName())->andReturnUsing(static function (string $title) use (&$variables): void {
            $variables['PAGETITLE'] = $title;
        });
        $game->shouldReceive('setMacroInAjaxWindow')->with('')->once();
        $game->shouldReceive('setMacroInAjaxWindow')->with('html/ship/borgSphere.twig')->once();
        $game->shouldReceive('setTemplateVar')->andReturnUsing(function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        request::setMockVars(['id' => $this->source->getId(), 'target' => $this->sphere->getId()]);
        try {
            $this->em->wrapInTransaction(fn () => $this->getContainer()->get(ShowBorgSphere::class)->handle($game));
        } finally {
            request::setMockVars(null);
        }
        $twig = new Environment($this->getContainer()->get(Environment::class)->getLoader(), ['cache' => false, 'strict_variables' => true]);
        $this->getContainer()->make(TwigHelper::class, ['environment' => $twig])->registerFiltersAndFunctions();
        return $twig->render('html/ajaxwindow.twig', $variables);
    }

    private function raidService(bool $success, int $attempts = 1, ?int $roll = null): BorgSphereRaidService
    {
        $random = $this->mock(StuRandom::class);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->times($attempts);
        if ($attempts !== 0) {
            $random->shouldReceive('rand')->with(1, 100)->times($attempts)->andReturn($roll ?? ($success ? 1 : 100));
            $random->shouldReceive('rand')->with($success ? 3 : 1, $success ? 5 : 3)->times($attempts)->andReturn($success ? 5 : 2);
            if (!$success) {
                $random->shouldReceive('array_rand')->twice()->andReturnUsing(static fn (array $crew): int => array_key_first($crew));
            }
        }
        $team = $this->getContainer()->make(BorgSphereRaidTeam::class, ['random' => $random]);
        return $this->getContainer()->make(BorgSphereRaidService::class, ['random' => $random, 'messages' => $messages, 'team' => $team]);
    }

    private function collect(BorgSphereRaidService $service, ?Ship $source = null): InformationWrapper
    {
        $information = new InformationWrapper();
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($source ?? $this->source);
        $this->em->wrapInTransaction(fn () => $service->collect($wrapper, $this->sphere, $information));
        return $information;
    }

    /** @return array<string, int> */
    private function settings(): array
    {
        return ['tick_seconds' => 3600, 'idle_chance_percent' => 20, 'intercept_chance_percent' => 40, 'movement_min_fields' => 1, 'movement_max_fields' => 7, 'normal_regen_min_percent' => 2, 'normal_regen_max_percent' => 10, 'transwarp_regen_bp' => 100, 'transwarp_escape_bp' => 8000, 'transwarp_initial_bp' => 1000, 'transwarp_floor_step_bp' => 1000, 'wake_percent' => 10, 'raid_cooldown_seconds' => 7200, 'raid_reward_min' => 3, 'raid_reward_max' => 5, 'raid_success_disabled' => 90, 'raid_success_active' => 90, 'raid_team_size' => 5, 'raid_loss_min' => 1, 'raid_loss_max' => 3, 'nanite_commodity_id' => 2, 'roam_radius' => 3];
    }
}
