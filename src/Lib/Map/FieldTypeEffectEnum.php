<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Stu\Orm\Entity\MapFieldTypeInterface;

enum FieldTypeEffectEnum: string
{
    case CLOAK_UNUSEABLE = 'CLOAK_UNUSEABLE';
    case WARPDRIVE_LEAK = 'WARPDRIVE_LEAK';
    case LSS_MALFUNCTION = 'LSS_MALFUNCTION';
    case NO_SPACECRAFT_COUNT = 'NO_SPACECRAFT_COUNT'; // don't ever show signature info
    case DUBIOUS_SPACECRAFT_COUNT = 'DUBIOUS_SPACECRAFT_COUNT'; // always show '!" sign, if at least one signature
    case NO_SUBSPACE_LINES = 'NO_SUBSPACE_LINES';

        //TODO: following not yet implemented
    case LSS_BLOCKADE = 'LSS_BLOCKADE';
    case NFS_MALFUNCTION_COOLDOWN = 'NFS_MALFUNCTION_COOLDOWN';
    case SHIELD_MALFUNCTION = 'SHIELD_MALFUNCTION';
    case REACTOR_LEAK = 'REACTOR_LEAK';
    case EPS_LEAK = 'EPS_LEAK';
    case HIT_CHANCE_INTERFERENCE = 'HIT_CHANCE_INTERFERENCE';
    case EVADE_CHANCE_INTERFERENCE = 'EVADE_CHANCE_INTERFERENCE';
    case ENERGY_WEAPON_BUFF = 'ENERGY_WEAPON_BUFF';
    case ENERGY_WEAPON_NERF = 'ENERGY_WEAPON_NERF';
    case REGENERATION_CHANCE = 'REGENERATION_CHANCE'; // small chance to regenerate warpdrive, eps, shields or reactor

    public function hasHandler(): bool
    {
        return match ($this) {
            self::CLOAK_UNUSEABLE,
            self::WARPDRIVE_LEAK => true,
            default => false
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::CLOAK_UNUSEABLE => 'Ausfall der Tarnung',
            self::WARPDRIVE_LEAK => 'Leck am Warpantrieb',
            self::LSS_MALFUNCTION => 'Störung der Langstreckensensoren',
            self::NO_SPACECRAFT_COUNT => 'Versteckte Signaturen',
            self::DUBIOUS_SPACECRAFT_COUNT => 'Verschleierung der Signaturen',
            self::NO_SUBSPACE_LINES => 'Verstecke Subraumspuren',
            self::LSS_BLOCKADE => 'Blockade der Langstreckensensoren',
            self::NFS_MALFUNCTION_COOLDOWN => 'Kurzzeitiger Ausfall der Nahbereichssensoren',
            self::SHIELD_MALFUNCTION => 'Störung der Schildemitter',
            self::REACTOR_LEAK => 'Leck am Reaktor',
            self::EPS_LEAK => 'Leck am EPS',
            self::HIT_CHANCE_INTERFERENCE => 'Beeinträchtigung der Zielerfassung',
            self::EVADE_CHANCE_INTERFERENCE => 'Beeinträchtigung der Manövrierbarkeit',
            self::ENERGY_WEAPON_BUFF => 'Steigerung des Energiewaffenschadens',
            self::ENERGY_WEAPON_NERF => 'Minderung des Energiewaffenschadens',
            self::REGENERATION_CHANCE => 'Geringe Chance auf energetischen Bonus'
        };
    }

    public function getFlightDestinationInfo(MapFieldTypeInterface $fieldType): ?string
    {
        $fieldTypeName = $fieldType->getName();

        return match ($this) {
            self::LSS_MALFUNCTION => sprintf("Interferenz im Subraum durch %s detektiert<br>
            Langstreckensensoren liefern keine verwertbaren Daten", $fieldTypeName),
            self::NO_SPACECRAFT_COUNT => sprintf('Subraumresonanzfeld durch %s stört die Phasenvarianz der Langstreckensensoren<br>
            Signaturanzeige ist nicht verfügbar', $fieldTypeName),
            self::DUBIOUS_SPACECRAFT_COUNT => sprintf('Unbekannte Interferenzmuster überlagern durch %s die Emissionssignale<br>
            Signaturanzeige wird verzerrt', $fieldTypeName),
            self::NO_SUBSPACE_LINES => sprintf('Subraumresonanzdichte blockiert durch %s die Erfassung von Bewegungsmustern <br>
            Subraumspuren bleiben verborgen', $fieldTypeName),
            self::LSS_BLOCKADE => sprintf('Der durch %s erzeugte Dichtegradient absorbiert Sensorimpulse im umgebenden Raum<b>
            Langstreckensensoren erfassen keine externen Kontakte', $fieldTypeName),
            self::HIT_CHANCE_INTERFERENCE => sprintf('Disruptive Fluktuationen durch %s in der Zielerfassungssensorik<br>
            Trefferchance verringert sich', $fieldTypeName),
            self::EVADE_CHANCE_INTERFERENCE => sprintf('Asymmetrische Feldverzerrungen durch %s beeinträchtig die Navigationssensoren<br>
            Manövrierbarkeit verringert sich', $fieldTypeName),
            self::ENERGY_WEAPON_BUFF => sprintf('Kohärenzverstärkung durch %s verstärkt den Energiewaffenoutput', $fieldTypeName),
            self::ENERGY_WEAPON_NERF => sprintf('Dekohärenz durch %s verstärkt den Energiewaffenoutput', $fieldTypeName),
            default => null
        };
    }
}
