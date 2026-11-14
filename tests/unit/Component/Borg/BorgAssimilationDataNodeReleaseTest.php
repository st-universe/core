<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\StuTestCase;

final class BorgAssimilationDataNodeReleaseTest extends StuTestCase
{
    /** @return iterable<string, array{int, int, bool, bool, bool, bool}> */
    public static function releases(): iterable
    {
        yield 'zero progress still permits one node' => [0, 1, true, false, false, true];
        yield 'nanites leave exactly one free slot' => [60, 3, true, false, false, true];
        yield 'nanites fill the hold before the node roll' => [60, 2, true, false, false, false];
        yield 'deletion and departure explicitly suppress nodes' => [60, 3, false, false, false, false];
        yield 'destroyed source releases a living target with a node chance' => [60, 3, true, true, false, true];
        yield 'destroyed target receives no release rewards' => [60, 3, true, false, true, false];
    }

    #[DataProvider('releases')]
    public function testNodeRewardIsCapacitySafeAndOccursOnlyOnceOnALivingShipRelease(int $elapsed, int $capacity, bool $rewardDataNode, bool $sourceDestroyed, bool $targetDestroyed, bool $expectedNode): void
    {
        $source = $this->ship(19, 0);
        $source->getCondition()->setIsDestroyed($sourceDestroyed);
        $ship = $this->ship(261, $capacity)->setName('K’T’inga');
        $ship->getCondition()->setIsDestroyed($targetDestroyed);
        $source->setTractoredShip($ship);
        $source->getSystems()->set(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value, new SpacecraftSystem()->setSpacecraft($source)->setSystemType(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON));
        $entry = new SpacecraftAssimilated()->setSpacecraft($ship)->start($source, 1000, 900);
        $repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $repository->shouldReceive('save')->once()->with($entry);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $storage = $this->mock(StorageManagerInterface::class);
        $random = $this->mock(StuRandom::class);
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $nanites = $elapsed === 0 || $targetDestroyed ? 0 : 2;
        $this->expectNanites($ship, $commodities, $storage, $random, $nanites);
        if ($rewardDataNode && !$targetDestroyed) {
            $configuration = new BorgSphereConfiguration()->setSettings(['assimilation_enabled' => 1, 'data_node_commodity_id' => 93]);
            $events->shouldReceive('getByShip')->once()->with($source)->andReturn(new BorgSphereEvent()->setConfiguration($configuration));
            $commodity = $this->commodity(93, 'Borg-Datenknoten');
            $commodities->shouldReceive('find')->once()->with(93)->andReturn($commodity);
        } else {
            $events->shouldNotReceive('getByShip');
        }
        if ($expectedNode) {
            $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(5);
            $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, 1)->andReturnUsing(static function () use ($ship, $commodity): void {
                $ship->getStorage()->set(93, new Storage()->setCommodity($commodity)->setAmount(1));
            });
        } else {
            $random->shouldNotReceive('rand')->with(1, 100);
        }
        if ($targetDestroyed) {
            $messages->shouldNotReceive('send');
        } else {
            $messages->shouldReceive('send')->once()->with(19, 261, Mockery::on(static fn (string $text): bool => str_contains($text, '1 Borg-Datenknoten') === $expectedNode), PrivateMessageFolderTypeEnum::SPECIAL_SHIP, $ship);
        }
        $release = new BorgAssimilationRelease($repository, $commodities, $storage, $messages, $random, $events);

        $release->release($entry, 1000 + $elapsed, $rewardDataNode);
        $release->release($entry, 1900, $rewardDataNode);

        self::assertFalse($entry->isActive());
        self::assertSame($nanites + ($expectedNode ? 1 : 0), $ship->getStorageSum());
        self::assertLessThanOrEqual($capacity, $ship->getStorageSum());
    }

    private function expectNanites(Ship $ship, CommodityRepositoryInterface $commodities, StorageManagerInterface $storage, StuRandom $random, int $amount): void
    {
        if ($amount === 0) {
            $commodities->shouldNotReceive('find')->with(76);
            $random->shouldNotReceive('rand')->with(1, 30);
            return;
        }
        $commodity = $this->commodity(76, 'Borg-Naniten');
        $commodities->shouldReceive('find')->once()->with(76)->andReturn($commodity);
        $random->shouldReceive('rand')->once()->with(1, 30)->andReturn(30);
        $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, $amount)->andReturnUsing(static function () use ($ship, $commodity, $amount): void {
            $ship->getStorage()->set(76, new Storage()->setCommodity($commodity)->setAmount($amount));
        });
    }

    private function commodity(int $id, string $name): Commodity
    {
        $commodity = $this->mock(Commodity::class);
        $commodity->shouldReceive('getId')->andReturn($id);
        $commodity->shouldReceive('getName')->andReturn($name);
        return $commodity;
    }

    private function ship(int $userId, int $capacity): Ship
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn($userId);
        $ship = new Ship()->setUser($user)->setRump(new SpacecraftRump()->setStorage($capacity));
        return $ship->setCondition(new SpacecraftCondition($ship));
    }
}
