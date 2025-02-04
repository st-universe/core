<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

enum FieldTypeEffectEnum: string
{
    case CLOAK_UNUSEABLE = 'CLOAK_UNUSEABLE';
    case WARPDRIVE_LEAK = 'WARPDRIVE_LEAK';
    case LSS_MALFUNCTION = 'LSS_MALFUNCTION';
    case NO_SPACECRAFT_COUNT = 'NO_SPACECRAFT_COUNT'; // don't ever show signature info
    case DUBIOUS_SPACECRAFT_COUNT = 'DUBIOUS_SPACECRAFT_COUNT'; // always show '!" sign, if at least one signature

    public function hasHandler(): bool
    {
        return match ($this) {
            self::NO_SPACECRAFT_COUNT => false,
            self::DUBIOUS_SPACECRAFT_COUNT => false,
            default => true
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::CLOAK_UNUSEABLE => 'Ausfall der Tarnung',
            self::WARPDRIVE_LEAK => 'Leck am Warpantrieb',
            self::LSS_MALFUNCTION => 'Störung der Langstreckensensoren',
            self::NO_SPACECRAFT_COUNT => 'Versteckte Signaturen',
            self::DUBIOUS_SPACECRAFT_COUNT => 'Verschleierung der Signaturen'
        };
    }
}
