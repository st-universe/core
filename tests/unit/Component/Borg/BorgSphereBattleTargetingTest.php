<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use ReflectionProperty;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackedBattleParty;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
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
