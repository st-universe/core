<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Override;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class TroopTransferUtility implements TroopTransferUtilityInterface
{
    public function __construct(
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private ShipTakeoverManagerInterface $shipTakeoverManager,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator
    ) {}

    #[Override]
    public function getFreeQuarters(SpacecraftInterface $ship): int
    {
        $free = $this->shipCrewCalculator->getMaxCrewCountByShip($ship) - $ship->getCrewCount();

        return max(0, $free);
    }

    #[Override]
    public function getBeamableTroopCount(SpacecraftInterface $spacecraft): int
    {
        $buildplan = $spacecraft->getBuildplan();
        if ($buildplan === null) {
            return 0;
        }

        $free = $spacecraft->getCrewCount() - $buildplan->getCrew();

        return max(0, $free);
    }

    #[Override]
    public function ownCrewOnTarget(UserInterface $user, EntityWithCrewAssignmentsInterface $target): int
    {
        return $target->getCrewAssignments()
            ->map(fn(CrewAssignmentInterface $crewAssignment): CrewInterface => $crewAssignment->getCrew())
            ->filter(fn(CrewInterface $crew): bool => $crew->getUser() === $user)
            ->count();
    }

    #[Override]
    public function foreignerCount(SpacecraftInterface $spacecraft): int
    {
        $count = 0;

        $user = $spacecraft->getUser();

        foreach ($spacecraft->getCrewAssignments() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser() !== $user) {
                $count++;
            }
        }

        return $count;
    }

    #[Override]
    public function assignCrew(CrewAssignmentInterface $crewAssignment, EntityWithCrewAssignmentsInterface $target): void
    {
        // TODO create CrewSlotAssignment
        $crewAssignment->clearAssignment()
            ->assign($target)
            ->setPosition(null);

        $target->getCrewAssignments()->add($crewAssignment);

        // clear any ShipTakeover
        if ($target instanceof ShipInterface) {
            $this->shipTakeoverManager->cancelTakeover(
                $target->getTakeoverPassive(),
                ', da das Schiff bemannt wurde'
            );
        }

        $this->shipCrewRepository->save($crewAssignment);
    }
}
