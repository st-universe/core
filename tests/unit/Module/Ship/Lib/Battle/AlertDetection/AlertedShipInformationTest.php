<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class AlertedShipInformationTest extends StuTestCase
{
    private AlertedShipInformationInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->subject = new AlertedShipInformation();
    }

    public static function provideAddAlertedShipsInfoData(): array
    {
        return [
            [ShipAlertStateEnum::ALERT_YELLOW, false, 'In Sektor 5|9 befindet sich 1 Flotte(n) auf [b][color=yellow]Alarm-Gelb![/color][/b]'],
            [ShipAlertStateEnum::ALERT_YELLOW, true, 'In Sektor 5|9 befindet sich 1 Einzelschiff(e) auf [b][color=yellow]Alarm-Gelb![/color][/b]'],
            [ShipAlertStateEnum::ALERT_RED, false, 'In Sektor 5|9 befindet sich 1 Flotte(n) auf [b][color=red]Alarm-Rot![/color][/b]'],
            [ShipAlertStateEnum::ALERT_RED, true, 'In Sektor 5|9 befindet sich 1 Einzelschiff(e) auf [b][color=red]Alarm-Rot![/color][/b]'],
        ];
    }

    #[DataProvider('provideAddAlertedShipsInfoData')]
    public function testAddAlertedShipsInfo(
        ShipAlertStateEnum $alertState,
        bool $isSingleton,
        string $expectedInfo
    ): void {

        $incomingShip = $this->mock(ShipInterface::class);
        $informations = $this->mock(InformationInterface::class);
        $battleParty = $this->mock(AlertStateBattleParty::class);

        $battleParties = [$battleParty];

        $incomingShip->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $incomingShip->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(9);

        $battleParty->shouldReceive('isSingleton')
            ->withNoArgs()
            ->andReturn($isSingleton);
        $battleParty->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn($alertState);

        $informations->shouldReceive('addInformation')
            ->with($expectedInfo . "\n")
            ->once();

        $this->subject->addAlertedShipsInfo(
            $incomingShip,
            $battleParties,
            $informations
        );
    }
}
