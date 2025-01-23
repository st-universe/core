<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Override;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

/**
 * Provides methods to retrieve the crew counts
 */
final class CrewCountRetriever implements CrewCountRetrieverInterface
{
    public function __construct(
        private CrewRepositoryInterface $crewRepository,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private CrewLimitCalculatorInterface $crewLimitCalculator,
        private CrewTrainingRepositoryInterface $crewTrainingRepository
    ) {}

    #[Override]
    public function getDebrisAndTradePostsCount(UserInterface $user): int
    {
        $count = $this->crewRepository
            ->getAmountByUserAndShipRumpCategory(
                $user,
                SpacecraftRumpEnum::SHIP_CATEGORY_ESCAPE_PODS
            );

        return $count + $this->shipCrewRepository->getAmountByUserAtTradeposts($user);
    }

    #[Override]
    public function getAssignedToShipsCount(UserInterface $user): int
    {
        return $this->shipCrewRepository->getAmountByUserOnShips($user);
    }

    #[Override]
    public function getInTrainingCount(UserInterface $user): int
    {
        return $this->crewTrainingRepository->getCountByUser($user);
    }

    #[Override]
    public function getRemainingCount(UserInterface $user): int
    {
        return max(
            0,
            $this->crewLimitCalculator->getGlobalCrewLimit($user) - $this->getAssignedCount($user) - $this->getInTrainingCount($user)
        );
    }

    #[Override]
    public function getAssignedCount(UserInterface $user): int
    {
        return $this->shipCrewRepository->getAmountByUser($user);
    }

    #[Override]
    public function getTrainableCount(UserInterface $user): int
    {
        return (int) ceil($this->crewLimitCalculator->getGlobalCrewLimit($user) / 10);
    }
}
