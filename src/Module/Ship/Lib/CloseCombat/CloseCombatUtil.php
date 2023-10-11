<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\CloseCombat;

use Stu\Component\Crew\CrewEnum;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;

final class CloseCombatUtil implements CloseCombatUtilInterface
{
    public const MAX_CREWMAN_PER_COMBAT = 5;

    public function getCombatGroup(ShipInterface $ship): array
    {
        $crewArray = array_map(
            fn (ShipCrewInterface $shipCrew) => $shipCrew->getCrew(),
            $ship->getCrewlist()->toArray()
        );

        usort(
            $crewArray,
            fn (CrewInterface $a, CrewInterface $b): int
            => CrewEnum::CREW_FIGHT_CAPABILITIES[$b->getType()]
                <=> CrewEnum::CREW_FIGHT_CAPABILITIES[$a->getType()]
        );

        return array_slice($crewArray, 0, self::MAX_CREWMAN_PER_COMBAT);
    }

    public function getCombatValue(array $combatGroup, FactionInterface $faction): int
    {
        $factionCombatScore = $faction->getCloseCombatScore();

        return array_reduce(
            $combatGroup,
            fn (int $value, CrewInterface $crew)
            => $value + CrewEnum::CREW_FIGHT_CAPABILITIES[$crew->getType()] * $factionCombatScore,
            0
        );
    }
}
