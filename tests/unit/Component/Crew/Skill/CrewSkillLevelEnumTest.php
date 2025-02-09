<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\StuTestCase;

class CrewSkillLevelEnumTest extends StuTestCase
{

    public static function getDataProvider(): array
    {
        return [
            [0, CrewSkillLevelEnum::RECRUIT],
            [99, CrewSkillLevelEnum::RECRUIT],
            [100, CrewSkillLevelEnum::CADET],
            [299, CrewSkillLevelEnum::CADET],
            [500, CrewSkillLevelEnum::ENSIGN],
            [999, CrewSkillLevelEnum::ENSIGN],
            [1000, CrewSkillLevelEnum::JUNIOR_LIEUTENANT],
            [1999, CrewSkillLevelEnum::JUNIOR_LIEUTENANT],
            [2000, CrewSkillLevelEnum::LIEUTENANT],
            [4999, CrewSkillLevelEnum::LIEUTENANT],
            [5000, CrewSkillLevelEnum::LIEUTENANT_COMMANDER],
            [9999, CrewSkillLevelEnum::LIEUTENANT_COMMANDER],
            [10000, CrewSkillLevelEnum::COMMANDER],
            [19999, CrewSkillLevelEnum::COMMANDER],
            [20000, CrewSkillLevelEnum::SENIOR_COMMANDER],
            [49999, CrewSkillLevelEnum::SENIOR_COMMANDER],
            [50000, CrewSkillLevelEnum::COMMODORE],
            [99999, CrewSkillLevelEnum::COMMODORE],
            [100000, CrewSkillLevelEnum::ADMIRAL],
            [1000000, CrewSkillLevelEnum::ADMIRAL],
            [10000000, CrewSkillLevelEnum::ADMIRAL]
        ];
    }

    #[DataProvider('getDataProvider')]
    public function testGetForExpertise(int $expertise, CrewSkillLevelEnum $expectedSkillLevel): void
    {
        $result = CrewSkillLevelEnum::getForExpertise($expertise);

        $this->assertEquals($expectedSkillLevel, $result);
    }
}
