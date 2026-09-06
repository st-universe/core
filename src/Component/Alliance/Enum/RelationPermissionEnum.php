<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Enum;

enum RelationPermissionEnum: int
{
    case FRIENDLY = 1;
    case SHARE_LIVE_MAP_POSITIONS = 2;

    public function getDescription(): string
    {
        return match ($this) {
            self::FRIENDLY => 'Als Freund behandeln',
            self::SHARE_LIVE_MAP_POSITIONS => 'Schiffspositionen auf der Live-Karte teilen'
        };
    }

    public function isAvailableFor(AllianceRelationTypeEnum $relationType): bool
    {
        return match ($this) {
            self::FRIENDLY => in_array(
                $relationType,
                [
                    AllianceRelationTypeEnum::TRADE,
                    AllianceRelationTypeEnum::FRIENDS,
                    AllianceRelationTypeEnum::VASSAL,
                    AllianceRelationTypeEnum::ALLIED
                ],
                true
            ),
            self::SHARE_LIVE_MAP_POSITIONS => $relationType === AllianceRelationTypeEnum::ALLIED
        };
    }

    public static function sanitize(int $permissions, AllianceRelationTypeEnum $relationType): int
    {
        $result = 0;
        foreach (self::cases() as $permission) {
            if (($permissions & $permission->value) !== 0 && $permission->isAvailableFor($relationType)) {
                $result |= $permission->value;
            }
        }

        return $result;
    }
}
