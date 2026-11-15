<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
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
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

final class BorgAssimilationDeadlineTest extends StuTestCase
{
    private BorgAssimilationRepositoryInterface&MockInterface $repository;
    private BorgAssimilationService $service;
    private Ship $source;
    private Ship $target;
    private SpacecraftAssimilated $entry;
    private int $now;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->now = 1007;
        $this->source = $this->ship(19)->setName('Kubus');
        $this->target = $this->ship(261)->setName('K’T’inga')->setMaxHull(1000)->setMaxShield(500);
        $this->source->setLocation(new StarSystemMap())->setTractoredShip($this->target);
        $this->target->setLocation($this->source->getLocation());
        $this->source->getSystems()->set(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value, new SpacecraftSystem()->setSpacecraft($this->source)->setSystemType(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON));
        $this->entry = new SpacecraftAssimilated()->setSpacecraft($this->target)->start($this->source, $this->now, 900);
        $this->repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $this->repository->shouldReceive('save');
        $this->repository->shouldReceive('lock');
        $this->repository->shouldReceive('getBySource')->with($this->source)->andReturn([$this->entry]);
        $this->repository->shouldReceive('getActive')->andReturn([$this->entry]);
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send');
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturnUsing(fn (): int => $this->now);
        $capture = new BorgAssimilationCapture($this->repository, $this->mock(PlayerRelationDeterminatorInterface::class), $this->mock(LeaveFleetInterface::class), $messages, $random, $clock);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->with(76)->andReturnNull();
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $events->shouldReceive('getByShip')->with($this->source)->andReturnNull();
        $release = new BorgAssimilationRelease($this->repository, $commodities, $this->mock(StorageManagerInterface::class), $messages, $random, $events);
        $fleets = $this->mock(FleetRepositoryInterface::class);
        $fleets->shouldReceive('prototype')->andReturnUsing(static fn (): Fleet => new Fleet());
        $fleets->shouldReceive('save');
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('getEpsSystemData', 'getWarpDriveSystemData', 'getReactorWrapper')->andReturnNull();
        $wrappers->shouldReceive('wrapShip')->with($this->target)->andReturn($wrapper);
        $history = $this->mock(EntryCreatorInterface::class);
        $history->shouldReceive('createEntry');
        $torpedoes = new BorgSphereTorpedoSupply($wrappers, $this->mock(ShipTorpedoManagerInterface::class), $this->mock(TorpedoTypeRepositoryInterface::class), $this->mock(TorpedoStorageRepositoryInterface::class), $random);
        $completion = new BorgAssimilationCompletion($this->repository, $fleets, $this->mock(StorageRepositoryInterface::class), $history, $messages, new BorgSphereEnergy($wrappers), $torpedoes);
        $this->service = new BorgAssimilationService($this->repository, $capture, $completion, $release, $clock);
    }

    public function testMinuteCronCannotCompleteBeforeTheExactFifteenMinutesElapsed(): void
    {
        $this->now = 1900;

        $this->service->process();

        self::assertSame(9333, $this->entry->getProgress());
        self::assertTrue($this->entry->isActive());
        self::assertFalse($this->entry->isComplete());
        self::assertSame(261, $this->target->getUser()->getId());
        $this->now = 1907;

        $this->service->process();

        self::assertSame(10000, $this->entry->getProgress());
        self::assertTrue($this->entry->isComplete());
        self::assertSame(19, $this->target->getUser()->getId());
    }

    /** @return iterable<string, array{int, int, bool}> */
    public static function rescueTimes(): iterable
    {
        yield 'one second before a minute aligned deadline' => [600, 1499, false];
        yield 'exactly at a minute aligned deadline' => [600, 1500, true];
        yield 'one second before the actual deadline' => [1007, 1906, false];
        yield 'exactly at the actual deadline' => [1007, 1907, true];
        yield 'after the deadline before the minute cron' => [1007, 1908, true];
    }

    #[DataProvider('rescueTimes')]
    public function testDeactivatingTheTractorUsesTheExactCaptureDeadlineEvenWithoutAProcessTick(int $start, int $now, bool $completed): void
    {
        $this->entry->start($this->source, $start, 900);
        $this->now = $now;
        $this->source->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_OFF);

        $this->service->releaseForSource($this->source);

        self::assertFalse($this->entry->isActive());
        self::assertSame($completed, $this->entry->isComplete());
        self::assertSame($completed ? 19 : 261, $this->target->getUser()->getId());
        self::assertNull($this->source->getTractoredShip());
    }

    public function testSuccessfulReleaseBeforeTheDeadlineCannotBeUndoneByTheFollowingMinuteCron(): void
    {
        $this->now = 1906;
        $this->service->releaseForSource($this->source);
        $frozenProgress = $this->entry->getProgress();
        $this->now = 1920;

        $this->service->process();

        self::assertSame($frozenProgress, $this->entry->getProgress());
        self::assertSame(9333, $frozenProgress);
        self::assertFalse($this->entry->isComplete());
        self::assertFalse($this->entry->isActive());
        self::assertSame(261, $this->target->getUser()->getId());
    }

    private function ship(int $userId): Ship
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn($userId);
        $user->shouldReceive('getName')->andReturn('Spieler' . $userId);
        $ship = $this->mock(Ship::class)->makePartial();
        $ship->__construct();
        $ship->shouldReceive('getSectorString')->andReturn('18|16');
        return $ship->setUser($user)->setCondition(new SpacecraftCondition($ship));
    }
}
