<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use org\bovigo\vfs\vfsStream;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftNfsItem;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;
use Stu\StuTestCase;

final class BorgAssimilationPresentationTest extends StuTestCase
{
    public function testFreedShipKeepsItsPartialOverlayAndUsesTheAssimilatedAsset(): void
    {
        $ship = new Ship()->setRump(new SpacecraftRump()->setId(4203));
        new SpacecraftAssimilated()->setSpacecraft($ship)->setProgress(2500);

        $state = $this->presentation()->getState($ship);

        self::assertSame(25.0, $state['progress']);
        self::assertSame('/assets/ships/assimilated/4203.png', $state['image']);
        self::assertFalse($state['active']);
        self::assertSame(0, $state['remaining']);
    }

    public function testMissingVariantFallsBackToTheNormalShipImage(): void
    {
        $ship = new Ship()->setRump(new SpacecraftRump()->setId(999999));
        new SpacecraftAssimilated()->setSpacecraft($ship)->setProgress(10000);

        $state = $this->presentation()->getState($ship);

        self::assertSame(100.0, $state['progress']);
        self::assertNull($state['image']);
    }

    public function testNbsSummariesResolveTheSameMinuteBasedProgressAsTheShipPage(): void
    {
        $target = $this->mock(SpacecraftNfsItem::class);
        $target->shouldReceive('getId')->once()->andReturn(77);
        $target->shouldReceive('getRumpId')->once()->andReturn(4203);
        $owner = $this->mock(User::class);
        $owner->shouldReceive('getId')->once()->andReturn(19);
        $source = new Ship()->setUser($owner);
        $assimilation = new SpacecraftAssimilated()->start($source, 1000, 900);
        $repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $repository->shouldReceive('getBySpacecraftId')->once()->with(77)->andReturn($assimilation);

        $state = $this->presentation($repository, 1120)->getState($target);

        self::assertSame(13.33, $state['progress']);
        self::assertTrue($state['active']);
        self::assertSame(780, $state['remaining']);
        self::assertSame(1000, $state['started']);
    }

    public function testRecapturedShipsRetainAFullFifteenMinuteCountdown(): void
    {
        $owner = $this->mock(User::class);
        $owner->shouldReceive('getId')->once()->andReturn(19);
        $source = new Ship()->setUser($owner);
        $ship = new Ship()->setRump(new SpacecraftRump()->setId(4203));
        new SpacecraftAssimilated()->setSpacecraft($ship)->setProgress(5000)->start($source, 1000, 900);

        $state = $this->presentation(now: 1450)->getState($ship);

        self::assertSame(73.33, $state['progress']);
        self::assertSame(450, $state['remaining']);
        self::assertSame(900, $state['duration']);
    }

    private function presentation(?BorgAssimilationRepositoryInterface $repository = null, int $now = 2000): BorgAssimilationPresentation
    {
        $assets = vfsStream::setup('assets', null, ['ships' => ['assimilated' => ['4203.png' => 'image']]]);
        $config = $this->mock(StuConfigInterface::class);
        $config->shouldReceive('getGameSettings->getAssetsPath')->andReturn($assets->url());
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->once()->andReturn($now);
        return new BorgAssimilationPresentation($repository ?? $this->mock(BorgAssimilationRepositoryInterface::class), $config, $clock);
    }
}
