<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\StuTestCase;

final class BorgDataNodeRewardTest extends StuTestCase
{
    /** @return iterable<string, array{array<string, int>, int, bool}> */
    public static function chances(): iterable
    {
        yield 'default five percent includes five' => [[], 5, true];
        yield 'default five percent excludes six' => [[], 6, false];
        yield 'configured chance includes boundary' => [['data_node_chance_percent' => 25], 25, true];
        yield 'configured chance excludes following number' => [['data_node_chance_percent' => 25], 26, false];
    }

    #[DataProvider('chances')]
    public function testOneDataNodeIsGrantedWithinTheConfiguredChance(array $settings, int $roll, bool $expected): void
    {
        $configuration = new BorgSphereConfiguration()->setSettings($settings + ['assimilation_enabled' => 1, 'data_node_commodity_id' => 93]);
        $ship = new Ship()->setRump(new SpacecraftRump()->setStorage(1));
        $commodity = $this->mock(Commodity::class);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->once()->with(93)->andReturn($commodity);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn($roll);
        $storage = $this->mock(StorageManagerInterface::class);
        if ($expected) {
            $commodity->shouldReceive('getName')->once()->andReturn('Borg-Datenknoten');
            $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, 1);
        } else {
            $storage->shouldNotReceive('upperStorage');
        }

        $report = new BorgDataNodeReward($commodities, $storage, $random)->grant($ship, $configuration);

        self::assertSame($expected ? '. Zusätzlich wurde 1 Borg-Datenknoten dem Lagerraum hinzugefügt' : '', $report);
    }

    /** @return iterable<string, array{?array<string, int>}> */
    public static function disabledSettings(): iterable
    {
        yield 'no source event' => [null];
        yield 'sphere never provides data nodes' => [['data_node_commodity_id' => 93]];
        yield 'unconfigured cube' => [['assimilation_enabled' => 1]];
        yield 'disabled drop' => [['assimilation_enabled' => 1, 'data_node_commodity_id' => 93, 'data_node_chance_percent' => 0]];
    }

    #[DataProvider('disabledSettings')]
    public function testUnavailableDataNodesDoNotReadStorageOrRoll(?array $settings): void
    {
        $configuration = $settings === null ? null : new BorgSphereConfiguration()->setSettings($settings);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldNotReceive('find');
        $storage = $this->mock(StorageManagerInterface::class);
        $storage->shouldNotReceive('upperStorage');
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');

        self::assertSame('', new BorgDataNodeReward($commodities, $storage, $random)->grant(new Ship(), $configuration));
    }

    public function testFullStorageDoesNotOverfillOrPerformTheNodeRoll(): void
    {
        $configuration = new BorgSphereConfiguration()->setSettings(['assimilation_enabled' => 1, 'data_node_commodity_id' => 93]);
        $commodity = $this->mock(Commodity::class);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->once()->with(93)->andReturn($commodity);
        $storage = $this->mock(StorageManagerInterface::class);
        $storage->shouldNotReceive('upperStorage');
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');
        $ship = new Ship()->setRump(new SpacecraftRump()->setStorage(0));

        self::assertSame('', new BorgDataNodeReward($commodities, $storage, $random)->grant($ship, $configuration));
    }

    public function testMissingCommodityDoesNotReserveCapacityOrRoll(): void
    {
        $configuration = new BorgSphereConfiguration()->setSettings(['assimilation_enabled' => 1, 'data_node_commodity_id' => 93]);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->once()->with(93)->andReturnNull();
        $storage = $this->mock(StorageManagerInterface::class);
        $storage->shouldNotReceive('upperStorage');
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');

        self::assertSame('', new BorgDataNodeReward($commodities, $storage, $random)->grant(new Ship(), $configuration));
    }
}
