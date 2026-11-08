<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class BorgSphereRaidTeam
{
    public function __construct(
        private CrewRepositoryInterface $crews,
        private CrewAssignmentRepositoryInterface $assignments,
        private SpacecraftShutdownInterface $shutdown,
        private SpacecraftMovementPublisherInterface $publisher,
        private StuRandom $random
    ) {}

    /** @return list<CrewAssignment> */
    public function select(Spacecraft $ship, int $amount): array
    {
        $amount = max(0, min(5, $amount, $ship->getExcessCrewCount()));
        $crew = array_values($ship->getCrewAssignments()->filter(fn (CrewAssignment $assignment): bool => $this->canLeave($assignment))->toArray());
        usort($crew, static fn (CrewAssignment $a, CrewAssignment $b): int => (($a->getSlot() === CrewTypeEnum::CREWMAN ? 0 : 1) <=> ($b->getSlot() === CrewTypeEnum::CREWMAN ? 0 : 1)));
        return array_slice($crew, 0, $amount);
    }

    /** @param list<CrewAssignment> $team
     * @return list<string>
     */
    public function assimilate(array $team, int $amount, SpacecraftWrapperInterface $wrapper): array
    {
        $names = [];
        $ship = $wrapper->get();
        $team = array_filter($team, fn (CrewAssignment $assignment): bool => $this->canLeave($assignment) && $ship->getCrewAssignments()->contains($assignment));
        $losses = min($amount, count($team), max(0, $ship->getExcessCrewCount()));
        for ($i = 0; $i < $losses; $i++) {
            $key = $this->random->array_rand($team);
            $assignment = $team[$key];
            unset($team[$key]);
            $crew = $assignment->getCrew();
            $names[] = $crew->getName();
            $ship->getCrewAssignments()->removeElement($assignment);
            $assignment->clearAssignment();
            $this->assignments->delete($assignment);
            $this->crews->delete($crew);
        }
        if ($ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::UPLINK)) {
            $this->publisher->publishCoverageChanged($ship);
        }
        if ($ship->getCrewAssignments()->isEmpty()) {
            $this->shutdown->shutdown($wrapper, true);
        }
        return $names;
    }
    private function canLeave(CrewAssignment $assignment): bool
    {
        return $assignment->getSlot() === null || $assignment->getSlot() === CrewTypeEnum::CREWMAN;
    }

}
