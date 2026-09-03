<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use PHPUnit\Framework\TestCase;

final class CrewRaceInputTest extends TestCase
{
    public function testNormalizesDefine(): void
    {
        self::assertSame('BAJORANER_UEBERSEE', CrewRaceInput::normalizeDefine('Bajoraner Übersee'));
        self::assertSame('BAJORANER_UEBERSEE', CrewRaceInput::normalizeDefine('Bajoraner__Übersee'));
    }

    public function testAllowsOnlyNormalizedDefineFormat(): void
    {
        self::assertTrue(CrewRaceInput::isValidDefine('BAJORANER_UEBERSEE'));
        self::assertFalse(CrewRaceInput::isValidDefine('BAJORANER UEBERSEE'));
        self::assertFalse(CrewRaceInput::isValidDefine('BAJORANER_2'));
        self::assertFalse(CrewRaceInput::isValidDefine('BAJORANER__UEBERSEE'));
    }
}
