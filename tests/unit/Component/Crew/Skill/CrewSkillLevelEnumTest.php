<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stu\Component\Faction\FactionEnum;

final class CrewSkillLevelEnumTest extends TestCase
{
    /** @return array<string, array{int, CrewSkillLevelEnum}> */
    public static function expertiseProvider(): array
    {
        return [
            'cadet' => [0, CrewSkillLevelEnum::CADET],
            'crewman' => [100, CrewSkillLevelEnum::CREWMAN],
            'ensign' => [300, CrewSkillLevelEnum::ENSIGN],
            'junior lieutenant' => [1_000, CrewSkillLevelEnum::JUNIOR_LIEUTENANT],
            'lieutenant' => [2_000, CrewSkillLevelEnum::LIEUTENANT],
            'lieutenant commander' => [5_000, CrewSkillLevelEnum::LIEUTENANT_COMMANDER],
            'commander' => [10_000, CrewSkillLevelEnum::COMMANDER],
            'captain' => [20_000, CrewSkillLevelEnum::CAPTAIN],
            'commodore' => [50_000, CrewSkillLevelEnum::COMMODORE],
            'admiral' => [100_000, CrewSkillLevelEnum::ADMIRAL],
            'above admiral' => [150_000, CrewSkillLevelEnum::ADMIRAL]
        ];
    }

    #[DataProvider('expertiseProvider')]
    public function testGetForExpertise(int $expertise, CrewSkillLevelEnum $expected): void
    {
        static::assertSame($expected, CrewSkillLevelEnum::getForExpertise($expertise));
    }

    /** @return array<string, array{CrewSkillLevelEnum, string, string, string, string, string}> */
    public static function descriptionProvider(): array
    {
        return [
            'cadet' => [CrewSkillLevelEnum::CADET, 'Kadett', 'Eredh', 'mangHom', 'Garheç', "Zok'la"],
            'crewman' => [CrewSkillLevelEnum::CREWMAN, 'Crewman', 'Crewman', 'Crewman', 'Crewman', 'Crewman'],
            'ensign' => [CrewSkillLevelEnum::ENSIGN, 'Ensign', 'Erein', 'lagh', "D'ja", 'Zok'],
            'junior lieutenant' => [CrewSkillLevelEnum::JUNIOR_LIEUTENANT, 'Lieutenant Junior Grade', "erei'Arrain", 'SogHom', 'Kara', 'Sub-Letek'],
            'lieutenant' => [CrewSkillLevelEnum::LIEUTENANT, 'Lieutenant', 'Arrain', "Sogh'Qov", 'Glen', 'Letek'],
            'lieutenant commander' => [CrewSkillLevelEnum::LIEUTENANT_COMMANDER, 'Lieutenant Commander', "khre'Arrain", "la' Hom", 'Gil', 'Sub-Taar'],
            'commander' => [CrewSkillLevelEnum::COMMANDER, 'Commander', "erei'Riov", "la'lv", 'Glinn', 'Taar'],
            'captain' => [CrewSkillLevelEnum::CAPTAIN, 'Captain', 'Riov', 'HoD', 'Gul', 'Daimon'],
            'commodore' => [CrewSkillLevelEnum::COMMODORE, 'Commodore', 'Enarrain', 'totlh', "Ri'ta Gul", 'Zok-Ress'],
            'admiral' => [CrewSkillLevelEnum::ADMIRAL, 'Admiral', 'Enriov', "Sa'", 'Legat', 'Ress']
        ];
    }

    #[DataProvider('descriptionProvider')]
    public function testGetDescription(
        CrewSkillLevelEnum $rank,
        string $federation,
        string $romulan,
        string $klingon,
        string $cardassian,
        string $ferengi
    ): void {
        static::assertSame($federation, $rank->getDescription(FactionEnum::FACTION_FEDERATION->value));
        static::assertSame($romulan, $rank->getDescription(FactionEnum::FACTION_ROMULAN->value));
        static::assertSame($klingon, $rank->getDescription(FactionEnum::FACTION_KLINGON->value));
        static::assertSame($cardassian, $rank->getDescription(FactionEnum::FACTION_CARDASSIAN->value));
        static::assertSame($ferengi, $rank->getDescription(FactionEnum::FACTION_FERENGI->value));
        static::assertSame($federation, $rank->getDescription(FactionEnum::FACTION_PAKLED->value));
    }

    /** @return array<string, array{CrewSkillLevelEnum, CrewSkillLevelEnum}> */
    public static function automaticPromotionProvider(): array
    {
        return [
            'cadet' => [CrewSkillLevelEnum::CADET, CrewSkillLevelEnum::CADET],
            'lieutenant commander' => [CrewSkillLevelEnum::LIEUTENANT_COMMANDER, CrewSkillLevelEnum::LIEUTENANT_COMMANDER],
            'commander' => [CrewSkillLevelEnum::COMMANDER, CrewSkillLevelEnum::LIEUTENANT_COMMANDER],
            'admiral' => [CrewSkillLevelEnum::ADMIRAL, CrewSkillLevelEnum::LIEUTENANT_COMMANDER]
        ];
    }

    #[DataProvider('automaticPromotionProvider')]
    public function testGetAutomaticPromotionTarget(
        CrewSkillLevelEnum $rank,
        CrewSkillLevelEnum $expected
    ): void {
        static::assertSame($expected, $rank->getAutomaticPromotionTarget());
    }

    /** @return array<string, array{CrewSkillLevelEnum, null|CrewSkillLevelEnum, null|int}> */
    public static function manualPromotionProvider(): array
    {
        return [
            'lieutenant commander' => [CrewSkillLevelEnum::LIEUTENANT_COMMANDER, CrewSkillLevelEnum::COMMANDER, 25],
            'commander' => [CrewSkillLevelEnum::COMMANDER, CrewSkillLevelEnum::CAPTAIN, 15],
            'captain' => [CrewSkillLevelEnum::CAPTAIN, CrewSkillLevelEnum::COMMODORE, 5],
            'commodore' => [CrewSkillLevelEnum::COMMODORE, CrewSkillLevelEnum::ADMIRAL, 3],
            'admiral' => [CrewSkillLevelEnum::ADMIRAL, null, null]
        ];
    }

    #[DataProvider('manualPromotionProvider')]
    public function testGetNextRankAndPromotionLimit(
        CrewSkillLevelEnum $rank,
        ?CrewSkillLevelEnum $nextRank,
        ?int $promotionLimit
    ): void {
        static::assertSame($nextRank, $rank->getNextRank());
        static::assertSame($promotionLimit, $nextRank?->getPromotionLimit());
    }
}
