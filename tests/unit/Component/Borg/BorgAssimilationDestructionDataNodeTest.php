<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\StuTestCase;

final class BorgAssimilationDestructionDataNodeTest extends StuTestCase
{
    /** @return iterable<string, array{string, ?int, int, bool}> */
    public static function destructionStates(): iterable
    {
        yield 'active assimilation includes the tenth percentile' => ['active', null, 10, true];
        yield 'active assimilation excludes the eleventh percentile' => ['active', null, 11, false];
        yield 'completed assimilation can yield a data node' => ['complete', null, 10, true];
        yield 'completion remains eligible after loss of its source' => ['orphaned', null, 10, true];
        yield 'frozen freed ship receives no new drop' => ['frozen', null, 0, false];
        yield 'ordinary ship or unassimilated cube receives no drop' => ['none', null, 0, false];
        yield 'configured chance includes its boundary' => ['active', 25, 25, true];
        yield 'configured chance excludes the next number' => ['complete', 25, 26, false];
        yield 'zero configured chance disables the drop' => ['active', 0, 0, false];
    }

    #[DataProvider('destructionStates')]
    public function testOnlyActiveOrCompleteAssimilationCanProduceOneConfiguredDestructionDrop(string $state, ?int $chance, int $roll, bool $expected): void
    {
        $ship = new Ship();
        $entry = $this->entry($ship, $state);
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $random = $this->mock(StuRandom::class);
        $storage = $this->mock(StorageManagerInterface::class);
        $eligible = $state === 'active' || $state === 'complete' || $state === 'orphaned';
        if ($eligible && $state !== 'orphaned') {
            $event = $chance === null ? null : new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setSettings(['data_node_destruction_chance_percent' => $chance]));
            $events->shouldReceive('getByShip')->once()->with($entry->getSource())->andReturn($event);
        } else {
            $events->shouldNotReceive('getByShip');
        }
        $commodity = $this->mock(Commodity::class);
        if ($eligible && $chance !== 0) {
            $commodities->shouldReceive('find')->once()->with(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE)->andReturn($commodity);
            $random->shouldReceive('rand')->once()->with(1, 100)->andReturn($roll);
        } else {
            $commodities->shouldNotReceive('find');
            $random->shouldNotReceive('rand');
        }
        if ($expected) {
            $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, 1);
        } else {
            $storage->shouldNotReceive('upperStorage');
        }
        $subject = new BorgAssimilationDestructionDataNode($commodities, $storage, $events, $random);

        self::assertSame($expected ? $commodity : null, $subject->grantForDestruction($ship));
    }

    public function testRemovingTheAssimilationRecordPreventsFurtherLootRolls(): void
    {
        $ship = new Ship();
        $entry = $this->entry($ship, 'complete');
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $events->shouldReceive('getByShip')->once()->with($entry->getSource())->andReturnNull();
        $commodity = $this->mock(Commodity::class);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->once()->with(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE)->andReturn($commodity);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(10);
        $storage = $this->mock(StorageManagerInterface::class);
        $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, 1);
        $subject = new BorgAssimilationDestructionDataNode($commodities, $storage, $events, $random);

        self::assertSame($commodity, $subject->grantForDestruction($ship));
        $ship->setAssimilation(null);
        self::assertNull($subject->grantForDestruction($ship));
    }

    public function testMissingCommodityDoesNotRollOrWriteCargo(): void
    {
        $ship = new Ship();
        $entry = $this->entry($ship, 'active');
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $events->shouldReceive('getByShip')->once()->with($entry->getSource())->andReturnNull();
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->once()->with(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE)->andReturnNull();
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');
        $storage = $this->mock(StorageManagerInterface::class);
        $storage->shouldNotReceive('upperStorage');

        self::assertNull(new BorgAssimilationDestructionDataNode($commodities, $storage, $events, $random)->grantForDestruction($ship));
    }

    private function entry(Ship $ship, string $state): ?SpacecraftAssimilated
    {
        if ($state === 'none') {
            return null;
        }
        $owner = $this->mock(User::class);
        $owner->shouldReceive('getId')->andReturn(19);
        $entry = new SpacecraftAssimilated()->setSpacecraft($ship)->start(new Ship()->setUser($owner), 1000, 900);
        if ($state === 'complete' || $state === 'orphaned') {
            $entry->complete(1900);
        }
        if ($state === 'orphaned') {
            $entry->setSource(null);
        } elseif ($state === 'frozen') {
            $entry->freeze(1300);
        }
        return $entry;
    }
}
