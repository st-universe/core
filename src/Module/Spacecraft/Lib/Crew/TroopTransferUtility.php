<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class TroopTransferUtility implements TroopTransferUtilityInterface
{
    public function __construct(
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private ShipTakeoverManagerInterface $shipTakeoverManager,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator
    ) {}

    #[\Override]
    public function getFreeQuarters(Spacecraft $ship): int
    {
        $free = $this->shipCrewCalculator->getMaxCrewCountByShip($ship) - $ship->getCrewCount();

        return max(0, $free);
    }

    #[\Override]
    public function getBeamableTroopCount(Spacecraft $spacecraft): int
    {
        $buildplan = $spacecraft->getBuildplan();
        if ($buildplan === null) {
            return 0;
        }

        $free = $spacecraft->getCrewCount() - $buildplan->getCrew();

        return max(0, $free);
    }

    #[\Override]
    public function ownCrewOnTarget(User $user, EntityWithCrewAssignmentsInterface $target): int
    {
        return $target->getCrewAssignments()
            ->map(fn (CrewAssignment $crewAssignment): Crew => $crewAssignment->getCrew())
            ->filter(fn (Crew $crew): bool => $crew->getUser()->getId() === $user->getId())
            ->count();
    }

    #[\Override]
    public function foreignerCount(Spacecraft $spacecraft): int
    {
        $count = 0;

        $user = $spacecraft->getUser();

        foreach ($spacecraft->getCrewAssignments() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser()->getId() !== $user->getId()) {
                $count++;
            }
        }

        return $count;
    }

    #[\Override]
    public function assignCrew(CrewAssignment $crewAssignment, EntityWithCrewAssignmentsInterface $target): void
    {
        // TODO create CrewSlotAssignment
        $crewAssignment->clearAssignment()
            ->assign($target)
            ->setSlot(null);

        $target->getCrewAssignments()->add($crewAssignment);

        // clear any ShipTakeover
        if ($target instanceof Ship) {
            $this->shipTakeoverManager->cancelTakeover(
                $target->getTakeoverPassive(),
                ', da das Schiff bemannt wurde'
            );
        }

        $this->shipCrewRepository->save($crewAssignment);
    }
}
