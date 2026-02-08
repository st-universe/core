<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

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

    #[\Override]
    public function getDebrisAndTradePostsCount(User $user): int
    {
        $count = $this->crewRepository
            ->getAmountByUserAndShipRumpCategory(
                $user,
                SpacecraftRumpCategoryEnum::ESCAPE_PODS
            );

        return $count + $this->shipCrewRepository->getAmountByUserAtTradeposts($user);
    }

    #[\Override]
    public function getAssignedToShipsCount(User $user): int
    {
        return $this->shipCrewRepository->getAmountByUserOnShips($user);
    }

    #[\Override]
    public function getInTrainingCount(User $user): int
    {
        return $this->crewTrainingRepository->getCountByUser($user);
    }

    #[\Override]
    public function getRemainingCount(User $user): int
    {
        return max(
            0,
            $this->crewLimitCalculator->getGlobalCrewLimit($user) - $this->getAssignedCount($user) - $this->getInTrainingCount($user)
        );
    }

    #[\Override]
    public function getAssignedCount(User $user): int
    {
        return $this->shipCrewRepository->getAmountByUser($user);
    }

    #[\Override]
    public function getTrainableCount(User $user): int
    {
        return (int) ceil($this->crewLimitCalculator->getGlobalCrewLimit($user) / 10);
    }
}
