<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

final class BorgSphereTorpedoSupplyTest extends StuTestCase
{
    private SpacecraftWrapperFactoryInterface&MockInterface $wrappers;
    private ShipTorpedoManagerInterface&MockInterface $torpedoes;
    private TorpedoTypeRepositoryInterface&MockInterface $types;
    private TorpedoStorageRepositoryInterface&MockInterface $storages;
    private StuRandom&MockInterface $random;
    private BorgSphereTorpedoSupply $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->torpedoes = $this->mock(ShipTorpedoManagerInterface::class);
        $this->types = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->storages = $this->mock(TorpedoStorageRepositoryInterface::class);
        $this->random = $this->mock(StuRandom::class);
        $this->subject = new BorgSphereTorpedoSupply($this->wrappers, $this->torpedoes, $this->types, $this->storages, $this->random);
    }

    public function testExistingTypeIsPreservedAndMixedStorageOnlyFillsTheTotalGap(): void
    {
        $ship = $this->ship();
        $active = new TorpedoType()->setLevel(1);
        $activeStorage = $this->stock($ship, $active, 35, true);
        $otherStorage = $this->stock($ship, new TorpedoType()->setLevel(2), 20, false);
        $this->expectRefill($ship, $active, 45);

        $this->subject->refill($ship);

        self::assertSame($active, $ship->getTorpedo());
        self::assertSame(80, $activeStorage->getStorage()->getAmount());
        self::assertSame(20, $otherStorage->getStorage()->getAmount());
        self::assertSame(100, $ship->getTotalTorpedoCount());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getMode());
    }

    public function testEmptyStorageUsesRandomMatchingLevelTypeAndCreatesAnActiveStock(): void
    {
        $ship = $this->ship();
        $first = new TorpedoType()->setLevel(1);
        $selected = new TorpedoType()->setLevel(1);
        $types = [17 => $first, 29 => $selected];
        $this->types->shouldReceive('getByLevel')->once()->with(1)->andReturn($types);
        $this->random->shouldReceive('array_rand')->once()->with($types)->andReturn(29);
        $this->expectRefill($ship, $selected, 100);

        $this->subject->refill($ship);

        self::assertCount(1, $ship->getTorpedoStorages());
        self::assertSame($selected, $ship->getTorpedo());
        self::assertSame(100, $ship->getTorpedoCount());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getMode());
    }

    public function testCompatibleInactiveStockIsActivatedAndReusedEvenAtZero(): void
    {
        $ship = $this->ship();
        $type = new TorpedoType()->setLevel(1);
        $storage = $this->stock($ship, $type, 0, false);
        $this->storages->shouldReceive('save')->once()->with($storage);
        $this->expectRefill($ship, $type, 100);

        $this->subject->refill($ship);

        self::assertCount(1, $ship->getTorpedoStorages());
        self::assertTrue($storage->isActive());
        self::assertSame(100, $ship->getTorpedoCount());
    }

    public function testFullCompatibleInactiveStockIsActivatedWithoutAddingTorpedoes(): void
    {
        $ship = $this->ship();
        $storage = $this->stock($ship, new TorpedoType()->setLevel(1), 100, false);
        $this->storages->shouldReceive('save')->once()->with($storage);
        $this->torpedoes->shouldNotReceive('changeTorpedo');

        $this->subject->refill($ship);

        self::assertTrue($storage->isActive());
        self::assertSame(100, $ship->getTorpedoCount());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getMode());
    }

    public function testFireableInactiveStockIsPreferredOverAnEmptyCompatibleStock(): void
    {
        $ship = $this->ship();
        $empty = $this->stock($ship, new TorpedoType()->setLevel(1), 0, false);
        $available = $this->stock($ship, new TorpedoType()->setLevel(1), 100, false);
        $this->storages->shouldReceive('save')->once()->with($available);
        $this->torpedoes->shouldNotReceive('changeTorpedo');

        $this->subject->refill($ship);

        self::assertFalse($empty->isActive());
        self::assertTrue($available->isActive());
        self::assertSame(100, $ship->getTorpedoCount());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getMode());
    }

    public function testFullIncompatibleStockDoesNotRequestOrActivateAnotherType(): void
    {
        $ship = $this->ship();
        $this->stock($ship, new TorpedoType()->setLevel(2), 100, false);
        $this->types->shouldNotReceive('getByLevel');
        $this->torpedoes->shouldNotReceive('changeTorpedo');

        $this->subject->refill($ship);

        self::assertSame(100, $ship->getTotalTorpedoCount());
        self::assertNull($ship->getTorpedo());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getMode());
    }

    /** @return iterable<string, array{int}> */
    public static function fullAmounts(): iterable
    {
        yield 'full' => [100];
        yield 'overfull' => [130];
    }

    #[DataProvider('fullAmounts')]
    public function testFullOrOverfullStockIsNeverIncreasedOrReduced(int $amount): void
    {
        $ship = $this->ship();
        $this->stock($ship, new TorpedoType()->setLevel(1), $amount, true);
        $this->torpedoes->shouldNotReceive('changeTorpedo');
        $this->wrappers->shouldNotReceive('wrapShip');

        $this->subject->refill($ship);

        self::assertSame($amount, $ship->getTotalTorpedoCount());
    }

    public function testIncompatibleStoredTypeCountsTowardCapacityAndUsesMatchingDefault(): void
    {
        $ship = $this->ship();
        $this->stock($ship, new TorpedoType()->setLevel(2), 20, false);
        $selected = new TorpedoType()->setLevel(1);
        $this->types->shouldReceive('getByLevel')->once()->with(1)->andReturn([41 => $selected]);
        $this->random->shouldReceive('array_rand')->once()->with([41 => $selected])->andReturn(41);
        $this->expectRefill($ship, $selected, 80);

        $this->subject->refill($ship);

        self::assertSame(100, $ship->getTotalTorpedoCount());
        self::assertSame(80, $ship->getTorpedoCount());
    }

    public function testMissingMatchingTypesLeavesEmptyStorageAndLauncherOff(): void
    {
        $ship = $this->ship();
        $this->types->shouldReceive('getByLevel')->once()->with(1)->andReturn([]);
        $this->random->shouldNotReceive('array_rand');
        $this->torpedoes->shouldNotReceive('changeTorpedo');

        $this->subject->refill($ship);

        self::assertSame(0, $ship->getTotalTorpedoCount());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getMode());
    }

    /** @return iterable<string, array{int, bool}> */
    public static function launcherStates(): iterable
    {
        yield 'disabled sphere' => [10, true];
        yield 'destroyed launcher' => [0, false];
    }

    #[DataProvider('launcherStates')]
    public function testRefillDoesNotActivateDisabledSphereOrBrokenLauncher(int $status, bool $disabled): void
    {
        $ship = $this->ship();
        $ship->getCondition()->setDisabled($disabled);
        $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->setStatus($status);
        $type = new TorpedoType()->setLevel(1);
        $this->stock($ship, $type, 20, true);
        $this->expectRefill($ship, $type, 80);

        $this->subject->refill($ship);

        self::assertSame(100, $ship->getTorpedoCount());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getMode());
    }

    /** @return iterable<string, array{bool, int}> */
    public static function unavailableLaunchers(): iterable
    {
        yield 'no launcher' => [false, 100];
        yield 'zero capacity' => [true, 0];
        yield 'negative capacity' => [true, -1];
    }

    #[DataProvider('unavailableLaunchers')]
    public function testMissingLauncherOrCapacityDoesNotRequestSupplies(bool $launcher, int $capacity): void
    {
        $ship = $this->ship($capacity);
        if (!$launcher) {
            $ship->getSystems()->remove(SpacecraftSystemTypeEnum::TORPEDO->value);
        }
        $this->types->shouldNotReceive('getByLevel');
        $this->torpedoes->shouldNotReceive('changeTorpedo');

        $this->subject->refill($ship);

        self::assertSame(0, $ship->getTotalTorpedoCount());
    }

    public function testHealthyStorageExtensionUsesTheExistingMaximumCapacity(): void
    {
        $ship = $this->ship();
        $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)->setStatus(10);
        $ship->getSystems()->set(SpacecraftSystemTypeEnum::TORPEDO_STORAGE->value, $system);
        $type = new TorpedoType()->setLevel(1);
        $this->stock($ship, $type, 50, true);
        $this->expectRefill($ship, $type, 250);

        $this->subject->refill($ship);

        self::assertSame(300, $ship->getTotalTorpedoCount());
        self::assertSame($ship->getMaxTorpedos(), $ship->getTotalTorpedoCount());
    }

    private function ship(int $capacity = 100): Ship
    {
        $ship = new Ship();
        $ship->setRump(new SpacecraftRump()->setTorpedoLevel(1)->setBaseTorpedoStorage($capacity))
            ->setCondition(new SpacecraftCondition($ship));
        $ship->getSystems()->set(SpacecraftSystemTypeEnum::TORPEDO->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType(SpacecraftSystemTypeEnum::TORPEDO)->setStatus(10)->setMode(SpacecraftSystemModeEnum::MODE_OFF));
        return $ship;
    }

    private function stock(Ship $ship, TorpedoType $type, int $amount, bool $active): TorpedoStorage
    {
        $storage = new TorpedoStorage()->setSpacecraft($ship)->setTorpedo($type)->setActive($active)->setStorage(new Storage()->setAmount($amount));
        $ship->addTorpedoStorage($storage);
        return $storage;
    }

    private function expectRefill(Ship $ship, TorpedoType $type, int $amount): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $this->wrappers->shouldReceive('wrapShip')->once()->with($ship)->andReturn($wrapper);
        $this->torpedoes->shouldReceive('changeTorpedo')->once()->with($wrapper, $amount, $type)->andReturnUsing(function () use ($ship, $type, $amount): void {
            $storage = $ship->getTorpedoStorageForType($type);
            if ($storage === null) {
                $this->stock($ship, $type, $amount, true);
            } else {
                $storage->getStorage()->setAmount($storage->getStorage()->getAmount() + $amount);
            }
        });
    }
}
