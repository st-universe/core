<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\Common\Collections\ArrayCollection;
use ReflectionProperty;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackedBattleParty;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgSphereBattleTargetingTest extends StuTestCase
{
    public function testConfirmationMakesDisabledSphereTargetableWithoutReturningFire(): void
    {
        [$ship, $context, $party] = $this->createParty();
        $ship->getCondition()->setDisabled(true);
        self::assertTrue($party->isDefeated());

        $context->begin($ship, true, false);
        self::assertFalse($party->isDefeated());
        self::assertCount(1, $party->getActiveMembers());
        self::assertCount(0, $party->getActiveMembers(true));

        $context->clear();
        self::assertTrue($party->isDefeated());
    }

    public function testDisablingDuringUnconfirmedVolleyRemovesTargetImmediately(): void
    {
        [$ship, $context, $party] = $this->createParty();
        $context->begin($ship, false, false);
        self::assertFalse($party->isDefeated());
        self::assertCount(1, $party->getActiveMembers(true));

        $ship->getCondition()->setDisabled(true);
        self::assertTrue($party->isDefeated());
        self::assertCount(0, $party->getActiveMembers(true));
    }

    public function testAutomaticSphereAttackKeepsOnlyVisibleLocalFleetMembers(): void
    {
        $location = new StarSystemMap();
        $source = new Ship()->setLocation($location);
        $context = new BorgSphereAttackContext();
        $context->beginAutomaticAttack($source);
        $ships = [];
        $wrappers = [];
        for ($id = 1; $id <= 5; $id++) {
            $ship = new Ship();
            new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
            $ship->setUser(new User())->setLocation($location)->setCondition(new SpacecraftCondition($ship))
                ->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP));
            $wrapper = $this->mock(SpacecraftWrapperInterface::class);
            $wrapper->shouldReceive('get')->andReturn($ship);
            $wrapper->shouldReceive('canFire')->andReturn(true);
            $ships[$id] = $ship;
            $wrappers[$id] = $wrapper;
        }
        $ships[3]->setLocation(new StarSystemMap());
        $ships[4]->getSystems()->set(SpacecraftSystemTypeEnum::WARPDRIVE->value, new SpacecraftSystem()->setSystemType(SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_ON));
        $ships[5]->getSystems()->set(SpacecraftSystemTypeEnum::RPG_MODULE->value, new SpacecraftSystem()->setSystemType(SpacecraftSystemTypeEnum::RPG_MODULE)->setData('{"invisible":true}'));
        $fleet = $this->mock(FleetWrapperInterface::class);
        $fleet->shouldReceive('getShipWrappers')->andReturn(new ArrayCollection($wrappers));
        $wrappers[1]->shouldReceive('getFleetWrapper')->andReturn($fleet);
        $party = new AttackedBattleParty($wrappers[1], $this->mock(StuRandom::class), $context);

        self::assertSame([1, 2], $party->getActiveMembers()->getKeys());
        self::assertSame([1, 2], $party->getActiveMembers(true)->getKeys());
        $context->clear();
        self::assertCount(5, $party->getActiveMembers());
    }

    /** @return array{Ship, BorgSphereAttackContext, AttackedBattleParty} */
    private function createParty(): array
    {
        $ship = new Ship();
        $ship->setUser(new User());
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, 101);
        $ship->setCondition(new SpacecraftCondition($ship));
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')->andReturnNull();
        $wrapper->shouldReceive('canFire')->andReturn(true);
        $context = new BorgSphereAttackContext();
        $party = new AttackedBattleParty($wrapper, $this->mock(StuRandom::class), $context);
        return [$ship, $context, $party];
    }
}
