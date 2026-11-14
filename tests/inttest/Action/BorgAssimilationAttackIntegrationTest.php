<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgAssimilationActionGuard;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Action\AttackSpacecraft\AttackSpacecraft;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCore;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhase;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;

final class BorgAssimilationAttackIntegrationTest extends ActionTestCase
{
    /** @return iterable<string, array{bool, bool, bool}> */
    public static function targetStates(): iterable
    {
        yield 'own active assimilation victim' => [true, false, false];
        yield 'own disabled assimilation victim' => [true, true, false];
        yield 'foreign active assimilation victim' => [false, false, false];
        yield 'foreign disabled assimilation victim' => [false, true, false];
        yield 'own shielded assimilation victim' => [true, false, true];
        yield 'foreign disabled shielded assimilation victim' => [false, true, true];
    }

    #[DataProvider('targetStates')]
    public function testNormalAttackDamagesAnAssimilationVictimWithoutEitherVictimOrCubeReturningFire(bool $ownTarget, bool $disabled, bool $shielded): void
    {
        $container = $this->getContainer();
        $em = $container->get(EntityManagerInterface::class);
        self::$testSession->setUserById(101);
        $attacker = $em->find(Ship::class, 78);
        $victim = $em->find(Ship::class, 42);
        $cube = $em->find(Ship::class, 77);
        $victim->setUser($em->find(User::class, $ownTarget ? 101 : 102));
        $cube->setUser($em->find(User::class, 10));
        $location = $em->find(Colony::class, 42)->getStarsystemMap();
        foreach ([$attacker, $victim, $cube] as $ship) {
            $ship->getFleet()?->getShips()->toArray();
            $ship->setFleet(null)->setIsFleetLeader(false)->setDockedTo(null)->setLocation($location);
            $ship->getBuildplan()->setCrew(0);
            $ship->setMaxHull(20000)->setMaxShield(2000);
            $ship->getCondition()->setHull(20000)->setShield(2000)->setDisabled(false)->setIsDestroyed(false);
            $this->system($em, $ship, SpacecraftSystemTypeEnum::NBS)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            $this->system($em, $ship, SpacecraftSystemTypeEnum::PHASER)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            $this->system($em, $ship, SpacecraftSystemTypeEnum::SHIELDS)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            $this->system($em, $ship, SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            if ($ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)) {
                $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
            $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($ship);
            $wrapper->getEpsSystemData()->setMaxEps(1000)->setEps(100)->update();
            $wrapper->getComputerSystemDataMandatory()->setHitChance(100)->setEvadeChance(0)->update();
            $wrapper->getEnergyWeaponSystemData()->setBaseDamage(50)->update();
        }
        $this->system($em, $cube, SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $cube->setTractoredShip($victim);
        $victim->getCondition()->setDisabled($disabled)->setShield($shielded ? 2000 : 0);
        $victim->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->setMode($shielded ? SpacecraftSystemModeEnum::MODE_ON : SpacecraftSystemModeEnum::MODE_OFF);
        $entry = new SpacecraftAssimilated()->setSpacecraft($victim)->start($cube, $container->get(StuTime::class)->time(), 900);
        $em->persist($entry);
        $attacker->getRump()->setPhaserVolleys(2)->setTorpedoVolleys(0);
        $weapon = $attacker->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getModule()->getWeapon();
        $weapon->setCriticalChance(0)->setVariance(0)->setFiringMode(EnergyWeaponPhase::FIRINGMODE_FOCUS);
        $em->flush();
        $factory = $container->get(SpacecraftWrapperFactoryInterface::class);
        $attackerWrapper = $factory->wrapShip($attacker);
        $victimWrapper = $factory->wrapShip($victim);
        $cubeWrapper = $factory->wrapShip($cube);
        self::assertTrue($attackerWrapper->canFire());
        self::assertTrue($victim->hasActiveWeapon());
        self::assertFalse($victimWrapper->canFire());
        self::assertTrue($cubeWrapper->canFire());
        request::setMockVars([AttackSpacecraft::ACTION_IDENTIFIER => 1, 'id' => $attacker->getId(), 'target' => $victim->getId()]);
        $container->get(BorgAssimilationActionGuard::class)->check(ModuleEnum::SHIP, AttackSpacecraft::ACTION_IDENTIFIER, $attacker->getUser());
        $alerts = $this->mock(AlertReactionFacadeInterface::class);
        $alerts->shouldNotReceive('doItAll');
        $core = $container->make(SpacecraftAttackCore::class, ['alertReactionFacade' => $alerts]);
        $action = $container->make(AttackSpacecraft::class, ['spacecraftAttackCore' => $core]);
        $context = $container->get(ActionContextFactoryInterface::class)->createActionContext($container->get(GameControllerInterface::class));

        $em->wrapInTransaction(fn () => $action->handle($context));

        if ($shielded) {
            self::assertLessThan(2000, $victim->getCondition()->getShield(), $context->getInfo()->getInformationsAsString());
        } else {
            self::assertLessThan(20000, $victim->getCondition()->getHull(), $context->getInfo()->getInformationsAsString());
        }
        self::assertSame(20000, $attacker->getCondition()->getHull());
        self::assertSame(20000, $cube->getCondition()->getHull());
        self::assertSame(2000, $cube->getCondition()->getShield());
        self::assertSame($disabled, $victim->getCondition()->isDisabled());
        self::assertTrue($victim->isBeingAssimilated());
        self::assertSame($cube, $victim->getTractoringSpacecraft());
        self::assertSame($victim, $cube->getTractoredShip());
        $attackerWrapper = $factory->wrapShip($attacker);
        $victimWrapper = $factory->wrapShip($victim);
        $cubeWrapper = $factory->wrapShip($cube);
        self::assertSame(98, $attackerWrapper->getEpsSystemData()->getEps());
        self::assertSame(100, $victimWrapper->getEpsSystemData()->getEps());
        self::assertSame(100, $cubeWrapper->getEpsSystemData()->getEps());
        self::assertSame(2, substr_count($context->getInfo()->getInformationsAsString(), ' feuert '));
        self::assertFalse($victimWrapper->canFire());
    }

    private function system(EntityManagerInterface $em, Ship $ship, SpacecraftSystemTypeEnum $type): SpacecraftSystem
    {
        if (!$ship->hasSpacecraftSystem($type)) {
            $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type);
            $ship->getSystems()->set($type->value, $system);
            $em->persist($system);
        }
        return $ship->getSpacecraftSystem($type)->setStatus(100);
    }
}
