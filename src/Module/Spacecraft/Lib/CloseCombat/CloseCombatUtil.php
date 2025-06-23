<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Override;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\SpacecraftInterface;

final class CloseCombatUtil implements CloseCombatUtilInterface
{
    public const int MAX_CREWMAN_PER_COMBAT = 5;

    #[Override]
    public function getCombatGroup(SpacecraftInterface $spacecraft): array
    {
        $crewArray = $spacecraft->getCrewAssignments()->toArray();

        usort(
            $crewArray,
            fn(CrewAssignmentInterface $a, CrewAssignmentInterface $b): int
            => $b->getFightCapability() <=> $a->getFightCapability()
        );

        return array_slice($crewArray, 0, self::MAX_CREWMAN_PER_COMBAT);
    }

    #[Override]
    public function getCombatValue(array $combatGroup, FactionInterface $faction): int
    {
        $factionCombatScore = $faction->getCloseCombatScore();

        return array_reduce(
            $combatGroup,
            fn(int $value, CrewAssignmentInterface $crewAssignment): int
            => $value + $crewAssignment->getFightCapability() * $factionCombatScore,
            0
        );
    }
}
