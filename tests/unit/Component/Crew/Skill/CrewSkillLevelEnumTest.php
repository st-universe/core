<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CrewSkillLevelEnumTest extends TestCase
{
    /** @return array<string, array{int, CrewSkillLevelEnum}> */
    public static function expertiseProvider(): array
    {
        return [
            'recruit' => [0, CrewSkillLevelEnum::RECRUIT],
            'cadet' => [100, CrewSkillLevelEnum::CADET],
            'ensign' => [300, CrewSkillLevelEnum::ENSIGN],
            'junior lieutenant' => [1_000, CrewSkillLevelEnum::JUNIOR_LIEUTENANT],
            'lieutenant' => [2_000, CrewSkillLevelEnum::LIEUTENANT],
            'lieutenant commander' => [5_000, CrewSkillLevelEnum::LIEUTENANT_COMMANDER],
            'commander' => [10_000, CrewSkillLevelEnum::COMMANDER],
            'senior commander' => [20_000, CrewSkillLevelEnum::SENIOR_COMMANDER],
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
}
