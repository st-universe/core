<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Stu\StuTestCase;

final class CrewTypeEnumTest extends StuTestCase
{
    public function testSpecialPositionsMatchTheRoleCrewColumns(): void
    {
        $this->assertSame(1, CrewTypeEnum::CAPTAIN->value);
        $this->assertSame(2, CrewTypeEnum::COMMAND->value);
        $this->assertSame(3, CrewTypeEnum::TACTIC->value);
        $this->assertSame(4, CrewTypeEnum::SCIENCE->value);
        $this->assertSame(5, CrewTypeEnum::TECHNICAL->value);
        $this->assertSame(6, CrewTypeEnum::NAVIGATION->value);
        $this->assertSame(7, CrewTypeEnum::CREWMAN->value);
    }
}
