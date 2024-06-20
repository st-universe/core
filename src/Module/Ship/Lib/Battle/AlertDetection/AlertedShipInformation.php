<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Orm\Entity\ShipInterface;

class AlertedShipInformation implements AlertedShipInformationInterface
{

    public function addAlertedShipsInfo(
        ShipInterface $incomingShip,
        array $alertedBattleParties,
        InformationInterface $informations
    ): void {

        $this->addInformation(
            $incomingShip,
            $alertedBattleParties,
            ShipAlertStateEnum::ALERT_RED,
            false,
            'Flotte(n) auf [b][color=red]Alarm-Rot![/color][/b]',
            $informations
        );
        $this->addInformation(
            $incomingShip,
            $alertedBattleParties,
            ShipAlertStateEnum::ALERT_RED,
            true,
            'Einzelschiff(e) auf [b][color=red]Alarm-Rot![/color][/b]',
            $informations
        );
        $this->addInformation(
            $incomingShip,
            $alertedBattleParties,
            ShipAlertStateEnum::ALERT_YELLOW,
            false,
            'Flotte(n) auf [b][color=yellow]Alarm-Gelb![/color][/b]',
            $informations
        );
        $this->addInformation(
            $incomingShip,
            $alertedBattleParties,
            ShipAlertStateEnum::ALERT_YELLOW,
            true,
            'Einzelschiff(e) auf [b][color=yellow]Alarm-Gelb![/color][/b]',
            $informations
        );
    }

    /** @param array<AlertStateBattleParty> $alertedBattleParties */
    private function addInformation(
        ShipInterface $incomingShip,
        array $alertedBattleParties,
        ShipAlertStateEnum $alertState,
        bool $isSingleton,
        string $format,
        InformationInterface $informations
    ): void {

        $count = count(array_filter(
            $alertedBattleParties,
            fn (AlertStateBattleParty $battlyParty) => $battlyParty->isSingleton() === $isSingleton
                && $battlyParty->getAlertState() === $alertState
        ));

        if ($count === 0) {
            return;
        }

        $informations->addInformation(sprintf(
            _('In Sektor %d|%d %s %d %s') . "\n",
            $incomingShip->getPosX(),
            $incomingShip->getPosY(),
            $count > 1 ? 'befinden sich' : 'befindet sich',
            $count,
            $format
        ));
    }
}
