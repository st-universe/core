<?php

declare(strict_types=1);

namespace Stu\Component\Quest\AwayMission;

enum AwayMissionResearchEnum: string
{
    case ENERGY_WEAPONS = 'energy_weapons';
    case SHIELDS = 'shields';
    case EPS = 'eps';
    case REACTOR = 'reactor';
    case IMPULSE_DRIVE = 'impulse_drive';
    case WARP_DRIVE = 'warp_drive';
    case SENSORS = 'sensors';
    case COMPUTER = 'computer';

    public function getDescription(): string
    {
        return match ($this) {
            self::ENERGY_WEAPONS => 'Energiewaffen',
            self::SHIELDS => 'Schilde',
            self::EPS => 'EPS-Gitter',
            self::REACTOR => 'Reaktor',
            self::IMPULSE_DRIVE => 'Impulsantrieb',
            self::WARP_DRIVE => 'Warpantrieb',
            self::SENSORS => 'Sensoren',
            self::COMPUTER => 'Computersysteme'
        };
    }

    public function getCommodityId(): int
    {
        return match ($this) {
            self::ENERGY_WEAPONS => 77,
            self::SHIELDS => 78,
            self::EPS => 79,
            self::REACTOR => 80,
            self::IMPULSE_DRIVE => 89,
            self::WARP_DRIVE => 90,
            self::SENSORS => 91,
            self::COMPUTER => 92
        };
    }

    public function getImprovedSystem(): string
    {
        return match ($this) {
            self::ENERGY_WEAPONS => 'die verbesserten Energiewaffen',
            self::SHIELDS => 'das verbesserte Schildsystem',
            self::EPS => 'das verbesserte EPS-Gitter',
            self::REACTOR => 'den verbesserten Reaktor',
            self::IMPULSE_DRIVE => 'den verbesserten Impulsantrieb',
            self::WARP_DRIVE => 'den verbesserten Warpantrieb',
            self::SENSORS => 'das verbesserte Sensorensystem',
            self::COMPUTER => 'die verbesserten Computersysteme'
        };
    }

    public function getFinding(): string
    {
        return match ($this) {
            self::ENERGY_WEAPONS => 'Die Naniten passten die Emitterabstimmung während des Feuerns an. Dadurch blieben die Energiestrahlen selbst unter hoher Last stärker gebündelt',
            self::SHIELDS => 'Die Naniten synchronisierten die Schildemitter und verteilten die auftreffende Energie auf benachbarte Schildsegmente',
            self::EPS => 'Die Naniten stabilisierten die Leitungsknoten und regelten Lastspitzen im EPS-Gitter schneller aus als die ursprünglichen Steuerungen',
            self::REACTOR => 'Die Naniten überwachten die Reaktionskammer und korrigierten Schwankungen im Energiefeld unmittelbar an der Quelle',
            self::IMPULSE_DRIVE => 'Die Naniten stimmten die Impulsspulen laufend nach. Die Messreihen zeigen eine bessere Schubausbeute bei gleichem Brennstoffverbrauch',
            self::WARP_DRIVE => 'Die Naniten glichen Abweichungen zwischen den Warpspulen aus. So ließ sich ein gleichmäßigeres Warpfeld mit geringeren Energieverlusten aufbauen',
            self::SENSORS => 'Die Naniten korrigierten Störsignale bereits in den Sensorarrays. Schwache Kontakte ließen sich dadurch deutlicher vom Hintergrundrauschen trennen',
            self::COMPUTER => 'Die Naniten verbanden beschädigte Verarbeitungsknoten neu und verteilten Rechenaufgaben auf freie Kapazitäten. Die Reaktionszeiten des Computers sanken deutlich'
        };
    }
}
