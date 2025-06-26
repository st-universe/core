<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Override;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class ColonyLimitCalculator implements ColonyLimitCalculatorInterface
{
    public function __construct(private ResearchRepositoryInterface $researchRepository, private ColonyRepositoryInterface $colonyRepository)
    {
    }

    #[Override]
    public function canColonizeFurtherColonyWithType(User $user, int $colonyType): bool
    {
        return $this->colonyRepository->getAmountByUser($user, $colonyType) < $this->researchRepository->getColonyTypeLimitByUser($user, $colonyType);
    }

    #[Override]
    public function getColonyLimitWithType(User $user, int $colonyType): int
    {
        return $this->researchRepository->getColonyTypeLimitByUser($user, $colonyType);
    }

    #[Override]
    public function getColonyCountWithType(User $user, int $colonyType): int
    {
        return $this->colonyRepository->getAmountByUser($user, $colonyType);
    }
}
