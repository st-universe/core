<?php

namespace Stu\Module\Spacecraft\Lib\Destruction;

use Stu\Orm\Entity\Spacecraft;

enum SpacecraftDestructionCauseEnum
{
    case FIELD_DAMAGE;
    case SHIP_FIGHT;
    case ALERT_YELLOW;
    case ALERT_RED;
    case ESCAPE_TRACTOR;
    case SELF_DESTRUCTION;
    case THOLIAN_WEB_IMPLOSION;
    case ORPHANIZED_TRADEPOST;
    case ANOMALY_DAMAGE;

    public function getHistoryEntryText(
        ?SpacecraftDestroyerInterface $destroyer,
        Spacecraft $destroyedSpacecraft
    ): string {

        $destroyerName = $destroyer === null ? '' : $destroyer->getName();
        $shipName = $destroyedSpacecraft->getName();
        $rumpName = $destroyedSpacecraft->getRump()->getName();
        $sector = $destroyedSpacecraft->getSectorString();

        return match ($this) {
            self::FIELD_DAMAGE => sprintf(
                'Die %s (%s) wurde beim Einflug in Sektor %s zerstört',
                $shipName,
                $rumpName,
                $sector
            ),
            self::SHIP_FIGHT => sprintf(
                'Die %s (%s) wurde in Sektor %s von der %s zerstört',
                $shipName,
                $rumpName,
                $sector,
                $destroyerName
            ),
            self::ALERT_YELLOW => sprintf(
                '[b][color=yellow]Alarm-Gelb:[/color][/b] %s',
                self::SHIP_FIGHT->getHistoryEntryText($destroyer, $destroyedSpacecraft),
            ),
            self::ALERT_RED => sprintf(
                '[b][color=red]Alarm-Rot:[/color][/b] %s',
                self::SHIP_FIGHT->getHistoryEntryText($destroyer, $destroyedSpacecraft),
            ),
            self::ESCAPE_TRACTOR => sprintf(
                'Die %s (%s) wurde bei einem Fluchtversuch in Sektor %s zerstört',
                $shipName,
                $rumpName,
                $sector
            ),
            self::SELF_DESTRUCTION => sprintf(
                'Die %s (%s) hat sich in Sektor %s selbst zerstört',
                $shipName,
                $rumpName,
                $sector
            ),
            self::THOLIAN_WEB_IMPLOSION => sprintf(
                'Die %s (%s) wurde in Sektor %s durch ein implodierendes Energienetz zerstört',
                $shipName,
                $rumpName,
                $sector
            ),
            self::ORPHANIZED_TRADEPOST => sprintf(
                'Der verlassene Handelsposten in Sektor %s ist zerfallen',
                $sector
            ),
            self::ANOMALY_DAMAGE => sprintf(
                'Die %s (%s) wurde beim Einflug in Sektor %s durch die Anomalie %s zerstört',
                $shipName,
                $rumpName,
                $sector,
                $destroyerName
            )
        };
    }
}
