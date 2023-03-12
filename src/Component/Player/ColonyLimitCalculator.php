<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class ColonyLimitCalculator implements ColonyLimitCalculatorInterface
{
    private ResearchRepositoryInterface $researchRepository;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ResearchRepositoryInterface $researchRepository,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->researchRepository = $researchRepository;
        $this->colonyRepository = $colonyRepository;
    }

    public function canColonizeFurtherColonyWithType(UserInterface $user, int $colonyType): bool
    {
        return $this->colonyRepository->getAmountByUser($user, $colonyType) < $this->researchRepository->getColonyTypeLimitByUser($user, $colonyType);
    }

    public function getColonyLimitWithType(UserInterface $user, int $colonyType): int
    {
        return $this->researchRepository->getColonyTypeLimitByUser($user, $colonyType);
    }

    public function getColonyCountWithType(UserInterface $user, int $colonyType): int
    {
        return $this->colonyRepository->getAmountByUser($user, $colonyType);
    }
}
