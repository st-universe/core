<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class ColonyLimitCalculator implements ColonyLimitCalculatorInterface
{
    public function __construct(private ResearchRepositoryInterface $researchRepository, private ColonyRepositoryInterface $colonyRepository)
    {
    }

    #[Override]
    public function canColonizeFurtherColonyWithType(UserInterface $user, int $colonyType): bool
    {
        return $this->colonyRepository->getAmountByUser($user, $colonyType) < $this->researchRepository->getColonyTypeLimitByUser($user, $colonyType);
    }

    #[Override]
    public function getColonyLimitWithType(UserInterface $user, int $colonyType): int
    {
        return $this->researchRepository->getColonyTypeLimitByUser($user, $colonyType);
    }

    #[Override]
    public function getColonyCountWithType(UserInterface $user, int $colonyType): int
    {
        return $this->colonyRepository->getAmountByUser($user, $colonyType);
    }
}
