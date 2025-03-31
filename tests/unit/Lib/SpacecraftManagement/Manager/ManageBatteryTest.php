<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
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

    /** @var MockInterface&PlayerRelationDeterminatorInterface */
    private MockInterface $playerRelationDeterminator;


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
        $this->playerRelationDeterminator = $this->mock(PlayerRelationDeterminatorInterface::class);

        $this->subject = new ManageBattery($this->privateMessageSender, $this->playerRelationDeterminator);
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
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenValueIsEmptyString(): void
    {
        $values = ['batt' => ['555' => '']];

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
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));

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
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectMessageWhenNotFriendAndShieldsOn(): void
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
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));
        $this->ship->shouldReceive('isShielded')
            ->withNoArgs()
            ->andReturn(true);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIPNAME');

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($this->ship->getUser(), $this->managerProvider->getUser())
            ->andReturn(false);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['SHIPNAME: Batterie konnte wegen aktivierter Schilde nicht aufgeladen werden.'], $msg);
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
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));
        $this->ship->shouldReceive('isShielded')
            ->withNoArgs()
            ->andReturn(false);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));


        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($this->ship->getUser(), $this->managerProvider->getUser())
            ->andReturn(true);

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
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));
        $this->ship->shouldReceive('isShielded')
            ->withNoArgs()
            ->andReturn(false);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(UserInterface::class));

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->with($this->ship->getUser(), $this->managerProvider->getUser())
            ->andReturn(true);

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

        $userMock = $this->mock(UserInterface::class);
        $userMock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipUserId);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($userMock);

        $this->ship->shouldReceive('isShielded')
            ->withNoArgs()
            ->andReturn(true);

        $managerProviderUserMock = $this->mock(UserInterface::class);
        $managerProviderUserMock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->managerProviderUserId);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($managerProviderUserMock);

        $this->playerRelationDeterminator->shouldReceive('isFriend')
            ->once()
            ->with($userMock, $managerProviderUserMock)
            ->andReturn(true);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(100);

        $this->managerProvider->shouldReceive('lowerEps')
            ->with(37)
            ->once();

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
                $this->ship
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

        $userMock = $this->mock(UserInterface::class);
        $userMock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipUserId);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($userMock);

        $this->ship->shouldReceive('isShielded')
            ->withNoArgs()
            ->andReturn(false);

        $managerProviderUserMock = $this->mock(UserInterface::class);
        $managerProviderUserMock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->managerProviderUserId);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($managerProviderUserMock);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(100);

        $this->managerProvider->shouldReceive('lowerEps')
            ->with(22)
            ->once();

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
                $this->ship
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

        $userMock = $this->mock(UserInterface::class);
        $userMock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipUserId);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($userMock);

        $this->ship->shouldReceive('isShielded')
            ->withNoArgs()
            ->andReturn(false);

        $managerProviderUserMock = $this->mock(UserInterface::class);
        $managerProviderUserMock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->managerProviderUserId);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($managerProviderUserMock);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(20);

        $this->managerProvider->shouldReceive('lowerEps')
            ->with(20)
            ->once();

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
                $this->ship
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

        $userMock = $this->mock(UserInterface::class);
        $userMock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipUserId);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($userMock);

        $this->ship->shouldReceive('isShielded')
            ->withNoArgs()
            ->andReturn(false);

        $managerProviderUserMock = $this->mock(UserInterface::class);
        $managerProviderUserMock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->managerProviderUserId);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($managerProviderUserMock);

        $this->managerProvider->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(20);

        $this->managerProvider->shouldReceive('lowerEps')
            ->with(12)
            ->once();

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
                $this->ship
            )
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Batterie um 12 Einheiten aufgeladen'], $msg);
    }
}
