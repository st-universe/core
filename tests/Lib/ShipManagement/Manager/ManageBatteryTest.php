<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ManageBatteryTest extends StuTestCase
{
    /** @var MockInterface&PrivateMessageSenderInterface */
    private MockInterface $privateMessageSender;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&EpsSystemData */
    private MockInterface $epsSystemData;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ManagerProviderInterface */
    private MockInterface $managerProvider;

    private int $shipId = 555;
    private int $shipUserId = 777;
    private int $managerProviderUserId = 123;

    private ManageBattery $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->epsSystemData = $this->mock(EpsSystemData::class);
        $this->ship = $this->mock(ShipInterface::class);
        $this->managerProvider = $this->mock(ManagerProviderInterface::class);

        $this->subject = new ManageBattery($this->privateMessageSender);
    }

    public function testManageExpectErrorWhenValuesNotPresent(): void
    {
        static::expectExceptionMessage('value array not existent');
        static::expectException(RuntimeException::class);

        $values = ['foo' => '42'];

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenEpsNotExistent(): void
    {
        $values = ['batt' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenNotInValues(): void
    {
        $values = ['batt' => ['5' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($this->epsSystemData);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenProviderEpsEmpty(): void
    {
        $values = ['batt' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($this->epsSystemData);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenEpsAlreadyFull(): void
    {
        $values = ['batt' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($this->epsSystemData);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->epsSystemData->shouldReceive('getBattery')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $this->epsSystemData->shouldReceive('getMaxBattery')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectLoadToMaxWhenValueIsLetterM(): void
    {
        $values = ['batt' => ['555' => 'm']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($this->epsSystemData);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipUserId);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(100);
        $this->managerProvider->shouldReceive('lowerEps')
            ->with(37)
            ->once();
        $this->managerProvider->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->managerProviderUserId);
        $this->managerProvider->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('providerName');
        $this->managerProvider->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $this->epsSystemData->shouldReceive('getBattery')
            ->withNoArgs()
            ->andReturn(5);
        $this->epsSystemData->shouldReceive('getMaxBattery')
            ->withNoArgs()
            ->andReturn(42);
        $this->epsSystemData->shouldReceive('setBattery')
            ->with(42)
            ->once()
            ->andReturn($this->epsSystemData);
        $this->epsSystemData->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                $this->managerProviderUserId,
                $this->shipUserId,
                'Die providerName lädt in Sektor SECTOR die Batterie der name um 37 Einheiten',
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
                'ship.php?SHOW_SHIP=1&id=555'
            )
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Batterie um 37 Einheiten aufgeladen'], $msg);
    }

    public function testManageExpectLoadingWhenValueIsNumeric(): void
    {
        $values = ['batt' => ['555' => '22']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($this->epsSystemData);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipUserId);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(100);
        $this->managerProvider->shouldReceive('lowerEps')
            ->with(22)
            ->once();
        $this->managerProvider->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->managerProviderUserId);
        $this->managerProvider->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('providerName');
        $this->managerProvider->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $this->epsSystemData->shouldReceive('getBattery')
            ->withNoArgs()
            ->andReturn(5);
        $this->epsSystemData->shouldReceive('getMaxBattery')
            ->withNoArgs()
            ->andReturn(42);
        $this->epsSystemData->shouldReceive('setBattery')
            ->with(27)
            ->once()
            ->andReturn($this->epsSystemData);
        $this->epsSystemData->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                $this->managerProviderUserId,
                $this->shipUserId,
                'Die providerName lädt in Sektor SECTOR die Batterie der name um 22 Einheiten',
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
                'ship.php?SHOW_SHIP=1&id=555'
            )
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Batterie um 22 Einheiten aufgeladen'], $msg);
    }

    public function testManageExpectPartialLoadingWhenProviderInsufficient(): void
    {
        $values = ['batt' => ['555' => '22']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($this->epsSystemData);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipUserId);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(20);
        $this->managerProvider->shouldReceive('lowerEps')
            ->with(20)
            ->once();
        $this->managerProvider->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->managerProviderUserId);
        $this->managerProvider->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('providerName');
        $this->managerProvider->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $this->epsSystemData->shouldReceive('getBattery')
            ->withNoArgs()
            ->andReturn(5);
        $this->epsSystemData->shouldReceive('getMaxBattery')
            ->withNoArgs()
            ->andReturn(42);
        $this->epsSystemData->shouldReceive('setBattery')
            ->with(25)
            ->once()
            ->andReturn($this->epsSystemData);
        $this->epsSystemData->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                $this->managerProviderUserId,
                $this->shipUserId,
                'Die providerName lädt in Sektor SECTOR die Batterie der name um 20 Einheiten',
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
                'ship.php?SHOW_SHIP=1&id=555'
            )
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Batterie um 20 Einheiten aufgeladen'], $msg);
    }

    public function testManageExpectPartialLoadingWhenValueToHigh(): void
    {
        $values = ['batt' => ['555' => '22']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($this->epsSystemData);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipUserId);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(20);
        $this->managerProvider->shouldReceive('lowerEps')
            ->with(12)
            ->once();
        $this->managerProvider->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->managerProviderUserId);
        $this->managerProvider->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('providerName');
        $this->managerProvider->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $this->epsSystemData->shouldReceive('getBattery')
            ->withNoArgs()
            ->andReturn(30);
        $this->epsSystemData->shouldReceive('getMaxBattery')
            ->withNoArgs()
            ->andReturn(42);
        $this->epsSystemData->shouldReceive('setBattery')
            ->with(42)
            ->once()
            ->andReturn($this->epsSystemData);
        $this->epsSystemData->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                $this->managerProviderUserId,
                $this->shipUserId,
                'Die providerName lädt in Sektor SECTOR die Batterie der name um 12 Einheiten',
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
                'ship.php?SHOW_SHIP=1&id=555'
            )
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Batterie um 12 Einheiten aufgeladen'], $msg);
    }
}
