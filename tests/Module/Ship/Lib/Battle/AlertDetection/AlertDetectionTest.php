<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class AlertDetectionTest extends StuTestCase
{
    /** @var MockInterface|AlertedShipsDetectionInterface */
    private $alertedShipsDetection;
    /** @var MockInterface|SkipDetectionInterface */
    private $skipDetection;
    /** @var MockInterface|BattlePartyFactoryInterface */
    private $battlePartyFactory;
    /** @var MockInterface|TrojanHorseNotifierInterface */
    private $trojanHorseNotifier;
    /** @var MockInterface|AlertedShipInformationInterface */
    private $alertedShipInformation;

    /** @var MockInterface|ShipInterface */
    private ShipInterface $incomingShip;

    private AlertDetectionInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->alertedShipsDetection = $this->mock(AlertedShipsDetectionInterface::class);
        $this->skipDetection = $this->mock(SkipDetectionInterface::class);
        $this->battlePartyFactory = $this->mock(BattlePartyFactoryInterface::class);
        $this->trojanHorseNotifier = $this->mock(TrojanHorseNotifierInterface::class);
        $this->alertedShipInformation = $this->mock(AlertedShipInformationInterface::class);

        //params
        $this->incomingShip = $this->mock(ShipInterface::class);

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
        $this->alertedShipsDetection->shouldReceive('getAlertedShipsOnLocation')
            ->with($this->incomingShip)
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
        $ship = $this->mock(ShipInterface::class);
        $battleParty = $this->mock(AlertStateBattleParty::class);
        $wrapperToSkip = $this->mock(ShipWrapperInterface::class);
        $shipToSkip = $this->mock(ShipInterface::class);
        $tractoringShip = $this->mock(ShipInterface::class);
        $informations = $this->mock(InformationInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $this->alertedShipsDetection->shouldReceive('getAlertedShipsOnLocation')
            ->with($this->incomingShip)
            ->once()
            ->andReturn(new ArrayCollection([$wrapperToSkip, $wrapper]));

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $wrapperToSkip->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($shipToSkip);

        $this->skipDetection->shouldReceive('isSkipped')
            ->with($this->incomingShip, $ship, $tractoringShip, Mockery::any())
            ->once()
            ->andReturn(false);
        $this->skipDetection->shouldReceive('isSkipped')
            ->with($this->incomingShip, $shipToSkip, $tractoringShip, Mockery::any())
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
