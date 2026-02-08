<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class AlertDetectionTest extends StuTestCase
{
    private MockInterface&AlertedShipsDetectionInterface $alertedShipsDetection;
    private MockInterface&SkipDetectionInterface $skipDetection;
    private MockInterface&BattlePartyFactoryInterface $battlePartyFactory;
    private MockInterface&TrojanHorseNotifierInterface $trojanHorseNotifier;
    private MockInterface&AlertedShipInformationInterface $alertedShipInformation;

    private MockInterface&Ship $incomingShip;

    private AlertDetectionInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->alertedShipsDetection = $this->mock(AlertedShipsDetectionInterface::class);
        $this->skipDetection = $this->mock(SkipDetectionInterface::class);
        $this->battlePartyFactory = $this->mock(BattlePartyFactoryInterface::class);
        $this->trojanHorseNotifier = $this->mock(TrojanHorseNotifierInterface::class);
        $this->alertedShipInformation = $this->mock(AlertedShipInformationInterface::class);

        //params
        $this->incomingShip = $this->mock(Ship::class);

        $this->subject = new AlertDetection(
            $this->alertedShipsDetection,
            $this->skipDetection,
            $this->battlePartyFactory,
            $this->trojanHorseNotifier,
            $this->alertedShipInformation,
        );
    }

    public function testDetectAlertedBattlePartiesExpectEmptyArrayWhenNoShipsOnLocation(): void
    {
        $location = $this->mock(Map::class);
        $user = $this->mock(User::class);

        $this->incomingShip->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);
        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->alertedShipsDetection->shouldReceive('getAlertedShipsOnLocation')
            ->with($location, $user)
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->subject->detectAlertedBattleParties(
            $this->incomingShip,
            $this->mock(InformationInterface::class),
            null
        );

        $this->assertEquals([], $result);
    }

    public function testDetectAlertedBattlePartiesExpectSuccessAndServiceCalls(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $location = $this->mock(Map::class);
        $user = $this->mock(User::class);
        $battleParty = $this->mock(AlertStateBattleParty::class);
        $wrapperToSkip = $this->mock(ShipWrapperInterface::class);
        $shipToSkip = $this->mock(Ship::class);
        $tractoringShip = $this->mock(Ship::class);
        $informations = $this->mock(InformationInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $this->incomingShip->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);
        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->alertedShipsDetection->shouldReceive('getAlertedShipsOnLocation')
            ->with($location, $user)
            ->once()
            ->andReturn(new ArrayCollection([$wrapperToSkip, $wrapper]));

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $this->skipDetection->shouldReceive('isSkipped')
            ->with($this->incomingShip, $wrapper, $tractoringShip, Mockery::any())
            ->once()
            ->andReturn(false);
        $this->skipDetection->shouldReceive('isSkipped')
            ->with($this->incomingShip, $wrapperToSkip, $tractoringShip, Mockery::any())
            ->once()
            ->andReturn(true);

        $this->battlePartyFactory->shouldReceive('createAlertStateBattleParty')
            ->with($wrapper)
            ->once()
            ->andReturn($battleParty);

        $this->trojanHorseNotifier->shouldReceive('informUsersAboutTrojanHorse')
            ->with($this->incomingShip, $tractoringShip, Mockery::any())
            ->once();

        $this->alertedShipInformation->shouldReceive('addAlertedShipsInfo')
            ->with($this->incomingShip, [123 => $battleParty], $informations)
            ->once();

        $result = $this->subject->detectAlertedBattleParties(
            $this->incomingShip,
            $informations,
            $tractoringShip
        );

        $this->assertEquals([123 => $battleParty], $result);
    }
}
