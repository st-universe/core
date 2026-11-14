<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use ReflectionProperty;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

final class BorgCompanionSupplyTest extends StuTestCase
{
    public function testOnlyOperationalLocalAssimilatedCompanionsAreRechargedAndLeaveWarp(): void
    {
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, 19);
        $location = new StarSystemMap();
        $fleet = new Fleet();
        $leader = $this->ship($user, $location, $fleet);
        $companion = $this->ship($user, $location, $fleet);
        $partial = $this->ship($user, $location, $fleet);
        $remote = $this->ship($user, new StarSystemMap(), $fleet);
        $foreign = $this->ship(new User(), $location, $fleet);
        $destroyed = $this->ship($user, $location, $fleet);
        $destroyed->getCondition()->setIsDestroyed(true);
        $disabled = $this->ship($user, $location, $fleet);
        $disabled->getCondition()->setDisabled(true);
        foreach ([$companion, $remote, $foreign, $destroyed, $disabled] as $ship) {
            new SpacecraftAssimilated()->setSpacecraft($ship)->start($leader, 100, 900)->complete(1000);
        }
        new SpacecraftAssimilated()->setSpacecraft($partial)->setProgress(5000);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('getEpsSystemData', 'getWarpDriveSystemData', 'getReactorWrapper')->once()->andReturnNull();
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrappers->shouldReceive('wrapShip')->with($companion)->twice()->andReturn($wrapper);
        $systems = $this->mock(SpacecraftSystemManagerInterface::class);
        $systems->shouldReceive('deactivate')->once()->with($wrapper, SpacecraftSystemTypeEnum::WARPDRIVE, true)
            ->andReturnUsing(static function () use ($companion): void {
                $companion->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            });
        $torpedoes = new BorgSphereTorpedoSupply($wrappers, $this->mock(ShipTorpedoManagerInterface::class), $this->mock(TorpedoTypeRepositoryInterface::class), $this->mock(TorpedoStorageRepositoryInterface::class), $this->mock(StuRandom::class));
        $supply = new BorgCompanionSupply(new BorgSphereEnergy($wrappers), $torpedoes, $wrappers, $systems);

        $supply->refill($leader);
        $supply->leaveWarp($leader);

        self::assertFalse($companion->isWarped());
        self::assertTrue($companion->getNbs());
        self::assertTrue($companion->getPhaserState());
        self::assertTrue($companion->getImpulseState());
        self::assertFalse($partial->getNbs());
        self::assertTrue($remote->isWarped());
        self::assertTrue($foreign->isWarped());
        self::assertTrue($destroyed->isWarped());
        self::assertTrue($disabled->isWarped());
    }

    private function ship(User $user, StarSystemMap $location, Fleet $fleet): Ship
    {
        $ship = new Ship()->setUser($user)->setLocation($location);
        $ship->setCondition(new SpacecraftCondition($ship))->setFleet($fleet);
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::IMPULSEDRIVE, SpacecraftSystemTypeEnum::WARPDRIVE] as $type) {
            $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(100)->setMode($type === SpacecraftSystemTypeEnum::WARPDRIVE ? SpacecraftSystemModeEnum::MODE_ON : SpacecraftSystemModeEnum::MODE_OFF));
        }
        return $ship;
    }
}
