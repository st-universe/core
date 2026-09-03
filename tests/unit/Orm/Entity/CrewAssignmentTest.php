<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stu\Component\Crew\CrewTypeEnum;

final class CrewAssignmentTest extends TestCase
{
    /** @return array<string, array{?CrewTypeEnum, int}> */
    public static function crewImageTypeProvider(): array
    {
        return [
            'Kommandierender Offizier' => [CrewTypeEnum::CAPTAIN, 1],
            '1. Offizier' => [CrewTypeEnum::COMMAND, 1],
            'Taktik' => [CrewTypeEnum::TACTIC, 2],
            'Wissenschaft' => [CrewTypeEnum::SCIENCE, 3],
            'Technik' => [CrewTypeEnum::TECHNICAL, 4],
            'Navigation' => [CrewTypeEnum::NAVIGATION, 5],
            'Crewman' => [CrewTypeEnum::CREWMAN, 6],
            'keine Position' => [null, 6]
        ];
    }

    #[DataProvider('crewImageTypeProvider')]
    public function testGetCrewImageType(?CrewTypeEnum $slot, int $expected): void
    {
        $assignment = new CrewAssignment();
        $assignment->setSlot($slot);

        self::assertSame($expected, $assignment->getCrewImageType());
    }
}
