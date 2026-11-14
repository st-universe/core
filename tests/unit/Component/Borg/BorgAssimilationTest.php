<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
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

final class BorgAssimilationTest extends StuTestCase
{
    /** @return iterable<string, array{int, int}> */
    public static function elapsedMinutes(): iterable
    {
        yield 'not started' => [0, 0];
        yield 'first minute pending' => [59, 0];
        yield 'first full minute' => [60, 666];
        yield 'fourteen minutes' => [899, 9333];
        yield 'completed after fifteen minutes' => [900, 10000];
        yield 'late tick' => [1800, 10000];
    }

    #[DataProvider('elapsedMinutes')]
    public function testProgressAdvancesOnlyForFullMinutesAndCannotExceedOneHundredPercent(int $elapsed, int $progress): void
    {
        $entry = new SpacecraftAssimilated()->start($this->ship(19), 1000, 900);

        self::assertSame($progress, $entry->getProgressAt(1000 + $elapsed));
        self::assertTrue($entry->isActive());
        self::assertFalse($entry->isComplete());
    }

    public function testReleaseFreezesProgressAndRecaptureContinuesFromTheScar(): void
    {
        $entry = new SpacecraftAssimilated()->start($this->ship(19), 1000, 900);
        $entry->freeze(1300);
        self::assertSame(3333, $entry->getProgress());
        self::assertSame(3333, $entry->getProgressAt(9000));
        self::assertFalse($entry->isActive());
        $entry->start($this->ship(19), 10000, 900);
        self::assertSame(3333, $entry->getInitialProgress());
        self::assertSame(3777, $entry->getProgressAt(10060));
        self::assertSame(9555, $entry->getProgressAt(10899));
        self::assertSame(10000, $entry->getProgressAt(10900));
        $entry->complete(11000);
        self::assertTrue($entry->isComplete());
        self::assertSame(10000, $entry->getProgressAt(12000));
    }

    public function testOnlyCompletedShipsOwnedByTheCapturingBorgAccountIgnoreMinimumCrew(): void
    {
        $source = $this->ship(19);
        $ship = $this->ship(261)->setBuildplan(new SpacecraftBuildplan()->setCrew(50));
        $entry = new SpacecraftAssimilated()->setSpacecraft($ship)->start($source, 1000, 900);
        self::assertFalse($ship->hasEnoughCrew());
        $entry->complete(1900);
        self::assertFalse($ship->hasEnoughCrew());
        $ship->setUser($source->getUser());
        self::assertTrue($ship->hasEnoughCrew());
    }

    public function testReleaseAwardsRoundedProportionalNanitesExactlyOnceAndKeepsTheOwner(): void
    {
        $source = $this->ship(19);
        $ship = $this->ship(261)->setName('K’T’inga');
        $source->setTractoredShip($ship);
        $this->tractor($source);
        $entry = new SpacecraftAssimilated()->setSpacecraft($ship)->start($source, 1000, 900)->setNaniteCommodityId(76)->setNaniteReward(1, 30);
        $repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $repository->shouldReceive('save')->once()->with($entry);
        $commodity = $this->mock(Commodity::class);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->once()->with(76)->andReturn($commodity);
        $storage = $this->mock(StorageManagerInterface::class);
        $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, 2);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 30)->andReturn(30);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once()->with(19, 261, Mockery::pattern('/6,66%.*2 Borg-Naniten/'), PrivateMessageFolderTypeEnum::SPECIAL_SHIP, $ship);
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $events->shouldReceive('getByShip')->once()->with($source)->andReturnNull();
        $release = new BorgAssimilationRelease($repository, $commodities, $storage, $messages, $random, $events);

        $release->release($entry, 1060);
        $release->release($entry, 2000);

        self::assertSame(261, $ship->getUser()->getId());
        self::assertSame(666, $entry->getProgress());
        self::assertNull($source->getTractoredShip());
        self::assertNull($ship->getTractoringSpacecraft());
        self::assertFalse($source->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM));
    }

    public function testCaptureIgnoresShieldsButRemovesTheShipFromItsFleetAndRetainsPlayerOwnership(): void
    {
        $source = $this->ship(19);
        $target = $this->ship(261)->setName('K’T’inga');
        $location = new StarSystemMap();
        $source->setLocation($location);
        $target->setLocation($location)->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::CRUISER));
        $location->getSpacecrafts()->add($target);
        foreach ([$source, $target] as $ship) {
            $ship->getSystems()->set(SpacecraftSystemTypeEnum::SHIELDS->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType(SpacecraftSystemTypeEnum::SHIELDS)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON));
            $ship->getCondition()->setShield(100);
        }
        $this->tractor($source);
        $target->setFleet(new Fleet());
        $repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $repository->shouldReceive('isAssimilateable')->twice()->with($target->getRump())->andReturn(true);
        $repository->shouldReceive('lock')->once()->with($target);
        $repository->shouldReceive('lock')->once()->with($source);
        $repository->shouldReceive('save')->once()->with(Mockery::type(SpacecraftAssimilated::class));
        $fleets = $this->mock(LeaveFleetInterface::class);
        $fleets->shouldReceive('leaveFleet')->once()->with($target)->andReturnUsing(static function (Ship $ship): bool {
            $ship->setFleet(null);
            return true;
        });
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->twice()->andReturn(false);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(33);
        $random->shouldReceive('array_rand')->once()->andReturn(0);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->once()->andReturn(1000);
        $event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setSettings(['assimilation_enabled' => 1, 'nanite_commodity_id' => 76]));
        $capture = new BorgAssimilationCapture($repository, $relations, $fleets, $messages, $random, $time);

        self::assertTrue($capture->tryCapture($event, $source));

        self::assertSame(261, $target->getUser()->getId());
        self::assertTrue($target->isBeingAssimilated());
        self::assertSame($source, $target->getTractoringSpacecraft());
        self::assertNull($target->getFleet());
        self::assertTrue($source->isShielded());
        self::assertTrue($target->isShielded());
    }

    /** @return iterable<string, array{bool}> */
    public static function warpedShips(): iterable
    {
        yield 'cube under warp' => [true];
        yield 'target under warp' => [false];
    }

    #[DataProvider('warpedShips')]
    public function testCaptureRequiresBothShipsOutsideWarp(bool $sourceWarped): void
    {
        $source = $this->ship(19);
        $target = $this->ship(261)->setRump(new SpacecraftRump());
        $location = new StarSystemMap();
        $source->setLocation($location);
        $target->setLocation($location);
        $location->getSpacecrafts()->add($target);
        $this->tractor($source);
        $warped = $sourceWarped ? $source : $target;
        $warped->getSystems()->set(SpacecraftSystemTypeEnum::WARPDRIVE->value, new SpacecraftSystem()->setSpacecraft($warped)->setSystemType(SpacecraftSystemTypeEnum::WARPDRIVE)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON));
        $repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $repository->shouldNotReceive('save', 'isAssimilateable');
        if ($sourceWarped) {
            $repository->shouldNotReceive('lock');
        } else {
            $repository->shouldReceive('lock')->once()->with($source);
        }
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('array_rand');
        $fleets = $this->mock(LeaveFleetInterface::class);
        $fleets->shouldNotReceive('leaveFleet');
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldNotReceive('send');
        $capture = new BorgAssimilationCapture($repository, $this->mock(PlayerRelationDeterminatorInterface::class), $fleets, $messages, $random, $this->mock(StuTime::class));
        $event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setSettings(['assimilation_enabled' => 1]));

        self::assertFalse($capture->tryCapture($event, $source));
        self::assertNull($target->getAssimilation());
        self::assertNull($source->getTractoredShip());
    }

    public function testCompletionTransfersCrewRepairsAndJoinsTheCubeFleetWithoutAddingNanites(): void
    {
        $source = $this->ship(19)->setName('Kubus');
        $ship = $this->ship(261)->setName('K’T’inga')->setMaxHull(1000)->setMaxShield(500);
        $ship->getCondition()->setHull(10)->setShield(0)->setDisabled(true);
        $source->setLocation(new StarSystemMap());
        $ship->setLocation($source->getLocation());
        $source->setTractoredShip($ship);
        $this->tractor($source);
        $crew = new Crew()->setUser($ship->getUser());
        $assignment = new CrewAssignment()->setCrew($crew)->setUser($ship->getUser());
        $ship->getCrewAssignments()->add($assignment);
        $entry = new SpacecraftAssimilated()->setSpacecraft($ship)->start($source, 1000, 900);
        $repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $repository->shouldReceive('save')->once()->with($entry);
        $fleets = $this->mock(FleetRepositoryInterface::class);
        $fleets->shouldReceive('prototype')->once()->andReturn(new Fleet());
        $fleets->shouldReceive('save')->once()->with(Mockery::type(Fleet::class));
        $history = $this->mock(EntryCreatorInterface::class);
        $history->shouldReceive('createEntry')->once()->with(HistoryTypeEnum::SHIP, Mockery::pattern('/K’T’inga.*Spieler Spieler261.*18\|16.*Kubus/'), 19, 261, $ship->getLocation());
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once();
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('getEpsSystemData', 'getWarpDriveSystemData', 'getReactorWrapper')->andReturn(null);
        $wrappers->shouldReceive('wrapShip')->once()->with($ship)->andReturn($wrapper);
        $torpedoes = new BorgSphereTorpedoSupply($wrappers, $this->mock(ShipTorpedoManagerInterface::class), $this->mock(TorpedoTypeRepositoryInterface::class), $this->mock(TorpedoStorageRepositoryInterface::class), $this->mock(StuRandom::class));
        $completion = new BorgAssimilationCompletion($repository, $fleets, $this->mock(StorageRepositoryInterface::class), $history, $messages, new BorgSphereEnergy($wrappers), $torpedoes);

        $completion->complete($entry, 1900);

        self::assertSame($source->getUser(), $ship->getUser());
        self::assertSame(19, $ship->getUserId());
        self::assertSame($source->getUser(), $crew->getUser());
        self::assertSame(19, $crew->getUserId());
        self::assertSame($source->getUser(), $assignment->getUser());
        self::assertSame($source->getFleet(), $ship->getFleet());
        self::assertSame($source, $ship->getFleet()->getLeadShip());
        self::assertSame(1000, $ship->getCondition()->getHull());
        self::assertSame(500, $ship->getCondition()->getShield());
        self::assertFalse($ship->getCondition()->isDisabled());
        self::assertTrue($entry->isComplete());
        self::assertFalse($entry->isActive());
    }

    public function testProcessPersistsMinuteProgressAndReleasesWhenTheEmitterIsNoLongerUsable(): void
    {
        $source = $this->ship(19);
        $ship = $this->ship(261);
        $source->setLocation(new StarSystemMap())->setTractoredShip($ship);
        $ship->setLocation($source->getLocation());
        $this->tractor($source);
        $entry = new SpacecraftAssimilated()->setSpacecraft($ship)->start($source, 1000, 900)->setNaniteReward(1, 30);
        $repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $repository->shouldReceive('getActive')->twice()->andReturn([$entry]);
        $repository->shouldReceive('lock')->twice()->with($ship);
        $repository->shouldReceive('lock')->twice()->with($source);
        $repository->shouldReceive('save')->twice()->with($entry);
        $commodity = $this->mock(Commodity::class);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $commodities->shouldReceive('find')->once()->with(76)->andReturn($commodity);
        $storage = $this->mock(StorageManagerInterface::class);
        $storage->shouldReceive('upperStorage')->once()->with($ship, $commodity, 4);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 30)->andReturn(30);
        $clock = $this->mock(StuTime::class);
        $now = 1060;
        $clock->shouldReceive('time')->andReturnUsing(static function () use (&$now): int {
            return $now;
        });
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $events->shouldReceive('getByShip')->once()->with($source)->andReturnNull();
        $release = new BorgAssimilationRelease($repository, $commodities, $storage, $messages, $random, $events);
        $capture = new BorgAssimilationCapture($repository, $this->mock(PlayerRelationDeterminatorInterface::class), $this->mock(LeaveFleetInterface::class), $messages, $random, $clock);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $torpedoes = new BorgSphereTorpedoSupply($wrappers, $this->mock(ShipTorpedoManagerInterface::class), $this->mock(TorpedoTypeRepositoryInterface::class), $this->mock(TorpedoStorageRepositoryInterface::class), $random);
        $completion = new BorgAssimilationCompletion($repository, $this->mock(FleetRepositoryInterface::class), $this->mock(StorageRepositoryInterface::class), $this->mock(EntryCreatorInterface::class), $messages, new BorgSphereEnergy($wrappers), $torpedoes);
        $service = new BorgAssimilationService($repository, $capture, $completion, $release, $clock);

        $service->process();
        self::assertSame(666, $entry->getProgress());
        self::assertTrue($entry->isActive());
        self::assertSame(261, $ship->getUser()->getId());
        $source->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(0);
        $now = 1120;
        $service->process();

        self::assertFalse($entry->isActive());
        self::assertSame(1333, $entry->getProgress());
        self::assertSame(261, $ship->getUser()->getId());
    }

    private function ship(int $userId): Ship
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn($userId);
        $user->shouldReceive('getName')->andReturn('Spieler' . $userId);
        $user->shouldReceive('isVacationRequestOldEnough')->andReturn(false);
        $ship = $this->mock(Ship::class)->makePartial();
        $ship->__construct();
        $ship->shouldReceive('getSectorString')->andReturn('18|16');
        $ship->setUser($user)->setCondition(new SpacecraftCondition($ship));
        return $ship;
    }

    private function tractor(Ship $ship): void
    {
        $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $ship->getSystems()->set(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value, $system);
    }
}
