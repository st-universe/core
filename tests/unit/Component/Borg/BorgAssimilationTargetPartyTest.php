<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackedBattleParty;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgAssimilationTargetPartyTest extends StuTestCase
{
    /** @return iterable<string, array{bool}> */
    public static function disabledStates(): iterable
    {
        yield 'active victim' => [false];
        yield 'disabled victim' => [true];
    }

    #[DataProvider('disabledStates')]
    public function testAnAssimilationVictimRemainsTargetableWithoutFiringOrCallingItsCube(bool $disabled): void
    {
        $ship = new Ship()->setUser(new User());
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, 42);
        $ship->setCondition(new SpacecraftCondition($ship)->setDisabled($disabled));
        $cube = $this->cube();
        new SpacecraftAssimilated()->setSpacecraft($ship)->start($cube, 100, 900);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('canFire')->andReturn(true);
        $wrapper->shouldNotReceive('getFleetWrapper', 'getTractoringSpacecraftWrapper');
        $party = new AttackedBattleParty($wrapper, $this->mock(StuRandom::class));

        self::assertSame([42 => $wrapper], $party->getActiveMembers()->toArray());
        self::assertFalse($party->isDefeated());
        self::assertTrue($party->getActiveMembers(true)->isEmpty());
        $ship->getCondition()->setIsDestroyed(true);
        self::assertTrue($party->isDefeated());
    }

    public function testACompletedBorgCompanionKeepsNormalDefenseBehavior(): void
    {
        $ship = new Ship()->setUser(new User());
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, 42);
        $ship->setCondition(new SpacecraftCondition($ship));
        $cube = $this->cube();
        new SpacecraftAssimilated()->setSpacecraft($ship)->start($cube, 100, 900)->complete(1000);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('canFire')->andReturn(true);
        $wrapper->shouldReceive('getFleetWrapper', 'getDockedToStationWrapper')->andReturnNull();
        $party = new AttackedBattleParty($wrapper, $this->mock(StuRandom::class));

        self::assertSame([42 => $wrapper], $party->getActiveMembers(true)->toArray());
        $ship->getCondition()->setDisabled(true);
        self::assertTrue($party->getActiveMembers()->isEmpty());
    }

    private function cube(): Ship
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(19);
        return new Ship()->setUser($user);
    }
}
