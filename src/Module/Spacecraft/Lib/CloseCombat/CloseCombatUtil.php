<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Override;
use Stu\Component\Crew\CrewEnum;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;

final class CloseCombatUtil implements CloseCombatUtilInterface
{
    public const int MAX_CREWMAN_PER_COMBAT = 5;

    #[Override]
    public function getCombatGroup(Spacecraft $spacecraft): array
    {
        $crewArray = $spacecraft->getCrewAssignments()->toArray();

        usort(
            $crewArray,
            fn(CrewAssignment $a, CrewAssignment $b): int
            => CrewEnum::CREW_FIGHT_CAPABILITIES[$b->getCrew()->getType()]
                <=> CrewEnum::CREW_FIGHT_CAPABILITIES[$a->getCrew()->getType()]
        );

        return array_slice($crewArray, 0, self::MAX_CREWMAN_PER_COMBAT);
    }

    #[Override]
    public function getCombatValue(array $combatGroup, Faction $faction): int
    {
        $factionCombatScore = $faction->getCloseCombatScore();

        return array_reduce(
            $combatGroup,
            fn(int $value, CrewAssignment $shipCrew): int
            => $value + CrewEnum::CREW_FIGHT_CAPABILITIES[$shipCrew->getCrew()->getType()] * $factionCombatScore,
            0
        );
    }
}
