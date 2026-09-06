<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Enum;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\StuTestCase;

class RelationPermissionEnumTest extends StuTestCase
{
    public static function providePermissionAvailability(): array
    {
        return [
            [RelationPermissionEnum::FRIENDLY,                 AllianceRelationTypeEnum::WAR,     false],
            [RelationPermissionEnum::FRIENDLY,                 AllianceRelationTypeEnum::PEACE,   false],
            [RelationPermissionEnum::FRIENDLY,                 AllianceRelationTypeEnum::TRADE,   true],
            [RelationPermissionEnum::FRIENDLY,                 AllianceRelationTypeEnum::FRIENDS, true],
            [RelationPermissionEnum::FRIENDLY,                 AllianceRelationTypeEnum::VASSAL,  true],
            [RelationPermissionEnum::FRIENDLY,                 AllianceRelationTypeEnum::ALLIED,  true],
            [RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS, AllianceRelationTypeEnum::VASSAL,  false],
            [RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS, AllianceRelationTypeEnum::ALLIED,  true]
        ];
    }

    #[DataProvider('providePermissionAvailability')]
    public function testPermissionAvailability(
        RelationPermissionEnum $permission,
        AllianceRelationTypeEnum $relationType,
        bool $expected
    ): void {
        self::assertSame($expected, $permission->isAvailableFor($relationType));
    }

    public function testSanitizeRemovesUnavailablePermissions(): void
    {
        $permissions =
            RelationPermissionEnum::FRIENDLY->value | RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS->value;

        self::assertSame(0, RelationPermissionEnum::sanitize($permissions, AllianceRelationTypeEnum::WAR));
        self::assertSame(
            RelationPermissionEnum::FRIENDLY->value,
            RelationPermissionEnum::sanitize(
                $permissions,
                AllianceRelationTypeEnum::VASSAL
            )
        );
        self::assertSame($permissions, RelationPermissionEnum::sanitize(
            $permissions,
            AllianceRelationTypeEnum::ALLIED
        ));
    }
}
