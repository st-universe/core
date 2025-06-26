<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ManageTorpedoTest extends StuTestCase
{
    private MockInterface&ShipTorpedoManagerInterface $shipTorpedoManager;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;

    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;
    private MockInterface&ManagerProviderInterface $managerProvider;
    private MockInterface&User $user;
    private MockInterface&TorpedoType $torpedoType;

    private int $shipId = 555;

    private ManageTorpedo $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipTorpedoManager = $this->mock(ShipTorpedoManagerInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);
        $this->user = $this->mock(User::class);
        $this->torpedoType = $this->mock(TorpedoType::class);
        $this->managerProvider = $this->mock(ManagerProviderInterface::class);

        $this->subject = new ManageTorpedo(
            $this->shipTorpedoManager,
            $this->privateMessageSender
        );
    }

    public function testManageExpectErrorWhenValuesNotPresent(): void
    {
        static::expectExceptionMessage('value array not existent');
        static::expectException(RuntimeException::class);

        $values = ['foo' => '42'];

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenNotInValues(): void
    {
        $values = ['torp' => ['5' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenValueNegative(): void
    {
        $values = ['torp' => ['555' => '-1']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenValueEqualsCurrentLoad(): void
    {
        $values = ['torp' => ['555' => 'm']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNoUnloadWhenForeignShip(): void
    {
        $shipOwner = $this->mock(User::class);
        $values = ['torp' => ['555' => '5']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(42);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($shipOwner);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNoUnloadWhenShipEmpty(): void
    {
        $values = ['torp' => ['555' => '5']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(42);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectUnloadWhenShipNotEmpty(): void
    {
        $torpedoCommodity = $this->mock(Commodity::class);
        $values = ['torp' => ['555' => '5']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->torpedoType->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedoCommodity);
        $this->torpedoType->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('torpedoname');

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('upperStorage')
            ->with($torpedoCommodity, 37)
            ->once();

        $this->shipTorpedoManager->shouldReceive('changeTorpedo')
            ->with($this->wrapper, -37)
            ->once();

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(42);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($this->torpedoType);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Es wurden 37 Torpedos des Typs torpedoname vom Schiff transferiert'], $msg);
    }

    public function testManageExpectNoLoadWhenShipEmptyAndNoTypeArrayExistent(): void
    {
        $values = ['torp' => ['555' => '5'], 'foo' => ['555' => '5']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(0);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNoLoadWhenShipEmptyAndNoTypeSelected(): void
    {
        $values = ['torp' => ['555' => '5'], 'torp_type' => ['123' => '7']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(0);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNoLoadWhenShipEmptyAndIllegalTypeSelected(): void
    {
        $allPossibleTorpedoTypes = [1 => $this->torpedoType];
        $values = ['torp' => ['555' => '5'], 'torp_type' => ['555' => '7']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getPossibleTorpedoTypes')
            ->withNoArgs()
            ->andReturn($allPossibleTorpedoTypes);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(0);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNoLoadWhenTorpedoTypeMissingOnProvider(): void
    {
        $allPossibleTorpedoTypes = [1 => $this->torpedoType];
        $values = ['torp' => ['555' => '5'], 'torp_type' => ['555' => '1']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getPossibleTorpedoTypes')
            ->withNoArgs()
            ->andReturn($allPossibleTorpedoTypes);

        $this->torpedoType->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $this->torpedoType->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('torpedoname');

        $this->managerProvider->shouldReceive('getStorage->get')
            ->with(1)
            ->andReturn(null);
        $this->managerProvider->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('providername');

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(0);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Es sind keine Torpedos des Typs torpedoname auf der providername vorhanden'], $msg);
    }

    public function testManageExpectReloadWhenShipAlreadyHasTorpedo(): void
    {
        $torpedoCommodity = $this->mock(Commodity::class);
        $providerStorage = $this->mock(Storage::class);
        $values = ['torp' => ['555' => '5']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->torpedoType->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $this->torpedoType->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedoCommodity);
        $this->torpedoType->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('torpedoname');

        $this->managerProvider->shouldReceive('getStorage->get')
            ->with(1)
            ->andReturn($providerStorage);
        $this->managerProvider->shouldReceive('lowerStorage')
            ->with($torpedoCommodity, 3)
            ->once();
        $this->managerProvider->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $this->managerProvider->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('providername');

        $providerStorage->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(3);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(1);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($this->torpedoType);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(777);
        $this->ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $this->shipTorpedoManager->shouldReceive('changeTorpedo')
            ->with($this->wrapper, 3)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                666,
                777,
                'Die providername hat in Sektor SECTOR 3 torpedoname auf die name transferiert',
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
                $this->ship
            )
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Es wurden 3 Torpedos des Typs torpedoname zum Schiff transferiert'], $msg);
    }

    public function testManageExpectLoadWhenShipIsEmpty(): void
    {
        $allPossibleTorpedoTypes = [1 => $this->torpedoType];
        $torpedoCommodity = $this->mock(Commodity::class);
        $providerStorage = $this->mock(Storage::class);
        $values = ['torp' => ['555' => '5'], 'torp_type' => ['555' => '1']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getPossibleTorpedoTypes')
            ->withNoArgs()
            ->andReturn($allPossibleTorpedoTypes);

        $this->torpedoType->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $this->torpedoType->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedoCommodity);
        $this->torpedoType->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('torpedoname');

        $this->managerProvider->shouldReceive('getStorage->get')
            ->with(1)
            ->andReturn($providerStorage);
        $this->managerProvider->shouldReceive('lowerStorage')
            ->with($torpedoCommodity, 5)
            ->once();
        $this->managerProvider->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $this->managerProvider->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('providername');

        $providerStorage->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(300);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->andReturn(0);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(777);
        $this->ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $this->shipTorpedoManager->shouldReceive('changeTorpedo')
            ->with($this->wrapper, 5, $this->torpedoType)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                666,
                777,
                'Die providername hat in Sektor SECTOR 5 torpedoname auf die name transferiert',
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
                $this->ship
            )
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Es wurden 5 Torpedos des Typs torpedoname zum Schiff transferiert'], $msg);
    }
}
