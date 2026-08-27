<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use PHPUnit\Framework\TestCase;

final class CrewRaceInputTest extends TestCase
{
    public function testNormalizesDefine(): void
    {
        static::assertSame('BAJORANER_UEBERSEE', CrewRaceInput::normalizeDefine('Bajoraner Übersee'));
        static::assertSame('BAJORANER_UEBERSEE', CrewRaceInput::normalizeDefine('Bajoraner__Übersee'));
    }

    public function testAllowsOnlyNormalizedDefineFormat(): void
    {
        static::assertTrue(CrewRaceInput::isValidDefine('BAJORANER_UEBERSEE'));
        static::assertFalse(CrewRaceInput::isValidDefine('BAJORANER UEBERSEE'));
        static::assertFalse(CrewRaceInput::isValidDefine('BAJORANER_2'));
        static::assertFalse(CrewRaceInput::isValidDefine('BAJORANER__UEBERSEE'));
    }
}
