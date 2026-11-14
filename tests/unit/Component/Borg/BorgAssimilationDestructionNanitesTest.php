<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\StuTestCase;

final class BorgAssimilationDestructionNanitesTest extends StuTestCase
{
    /** @return iterable<string, array{string, int, int, int}> */
    public static function states(): iterable
    {
        yield 'partial uses current minute instead of last saved progress' => ['active', 60, 30, 2];
        yield 'a single nanite rounds up at partial progress' => ['active', 60, 1, 1];
        yield 'full assimilation yields the complete random amount' => ['complete', 900, 30, 30];
        yield 'elapsed active assimilation also yields the complete amount' => ['active', 900, 13, 13];
        yield 'zero progress yields no nanites' => ['active', 59, 0, 0];
        yield 'released scarring was already rewarded' => ['frozen', 300, 0, 0];
        yield 'ordinary spacecraft yields no nanites' => ['none', 0, 0, 0];
    }

    #[DataProvider('states')]
    public function testOnlyOngoingOrCompletedAssimilationProducesProportionalDestructionLoot(string $state, int $elapsed, int $roll, int $expected): void
    {
        $ship = new Ship();
        $entry = $this->entry($ship, $state, $elapsed);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $storage = $this->mock(StorageManagerInterface::class);
        $random = $this->mock(StuRandom::class);
        if ($expected > 0) {
            $commodity = $this->mock(Commodity::class);
            $commodities->shouldReceive('find')->once()->with(76)->andReturn($commodity);
            $random->shouldReceive('rand')->once()->with(1, 30)->andReturn($roll);
            $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, $expected);
        } else {
            $commodities->shouldNotReceive('find');
            $random->shouldNotReceive('rand');
            $storage->shouldNotReceive('upperStorage');
        }
        $clock = $this->mock(StuTime::class);
        if ($state === 'active' || $state === 'complete') {
            $clock->shouldReceive('time')->once()->andReturn(1000 + $elapsed);
        } else {
            $clock->shouldNotReceive('time');
        }
        $helper = new BorgAssimilationDestructionNanites(new BorgAssimilationNanites($commodities, $storage, $random), $clock);

        self::assertSame($expected, $helper->grantForDestruction($ship));
        if ($entry !== null) {
            self::assertSame($state === 'complete' ? 10000 : ($state === 'frozen' ? 3333 : 0), $entry->getProgress());
        }
    }

    public function testNaniteCommodityAndRewardRangeComeFromTheAssimilationRecord(): void
    {
        $ship = new Ship();
        $entry = $this->entry($ship, 'active', 60)->setNaniteCommodityId(321)->setNaniteReward(7, 17);
        $commodity = $this->mock(Commodity::class);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->once()->with(321)->andReturn($commodity);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(7, 17)->andReturn(11);
        $storage = $this->mock(StorageManagerInterface::class);
        $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, 6);

        self::assertSame(6, new BorgAssimilationNanites($commodities, $storage, $random)->grant($entry, 5000));
    }

    private function entry(Ship $ship, string $state, int $elapsed): ?SpacecraftAssimilated
    {
        if ($state === 'none') {
            return null;
        }
        $owner = $this->mock(User::class);
        $owner->shouldReceive('getId')->andReturn(19);
        $entry = new SpacecraftAssimilated()->setSpacecraft($ship)->start(new Ship()->setUser($owner), 1000, 900)->setNaniteCommodityId(76);
        if ($state === 'complete') {
            $entry->complete(1000 + $elapsed);
        } elseif ($state === 'frozen') {
            $entry->freeze(1000 + $elapsed);
        }
        return $entry;
    }
}
