<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Orm\Entity\Spacecraft;

class AlertedShipInformation implements AlertedShipInformationInterface
{
    #[\Override]
    public function addAlertedShipsInfo(
        Spacecraft $incomingSpacecraft,
        array $alertedBattleParties,
        InformationInterface $informations
    ): void {

        $this->addInformation(
            $incomingSpacecraft,
            $alertedBattleParties,
            SpacecraftAlertStateEnum::ALERT_RED,
            false,
            'Flotte(n) auf [b][color=red]Alarm-Rot![/color][/b]',
            $informations
        );
        $this->addInformation(
            $incomingSpacecraft,
            $alertedBattleParties,
            SpacecraftAlertStateEnum::ALERT_RED,
            true,
            'Einzelschiff(e) auf [b][color=red]Alarm-Rot![/color][/b]',
            $informations
        );
        $this->addInformation(
            $incomingSpacecraft,
            $alertedBattleParties,
            SpacecraftAlertStateEnum::ALERT_YELLOW,
            false,
            'Flotte(n) auf [b][color=yellow]Alarm-Gelb![/color][/b]',
            $informations
        );
        $this->addInformation(
            $incomingSpacecraft,
            $alertedBattleParties,
            SpacecraftAlertStateEnum::ALERT_YELLOW,
            true,
            'Einzelschiff(e) auf [b][color=yellow]Alarm-Gelb![/color][/b]',
            $informations
        );
    }

    /** @param array<AlertStateBattleParty> $alertedBattleParties */
    private function addInformation(
        Spacecraft $incomingSpacecraft,
        array $alertedBattleParties,
        SpacecraftAlertStateEnum $alertState,
        bool $isSingleton,
        string $format,
        InformationInterface $informations
    ): void {

        $count = count(array_filter(
            $alertedBattleParties,
            fn(AlertStateBattleParty $battlyParty): bool => $battlyParty->isSingleton() === $isSingleton
                && $battlyParty->getAlertState() === $alertState
        ));

        if ($count === 0) {
            return;
        }

        $informations->addInformation(sprintf(
            _('In Sektor %d|%d %s %d %s') . "\n",
            $incomingSpacecraft->getPosX(),
            $incomingSpacecraft->getPosY(),
            $count > 1 ? 'befinden sich' : 'befindet sich',
            $count,
            $format
        ));
    }
}
