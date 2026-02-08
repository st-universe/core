<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyClassResearch;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyClassResearchRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class ColonizationChecker implements ColonizationCheckerInterface
{
    public function __construct(private ResearchedRepositoryInterface $researchedRepository, private ColonyClassResearchRepositoryInterface $colonyClassResearchRepository, private ColonyLimitCalculatorInterface $colonyLimitCalculator) {}

    #[\Override]
    public function canColonize(User $user, Colony $colony): bool
    {
        if (!$colony->isFree()) {
            return false;
        }

        $colonyClass = $colony->getColonyClass();

        if (!$this->colonyLimitCalculator->canColonizeFurtherColonyWithType($user, $colonyClass->getType())) {
            return false;
        }

        $researchIds = array_map(
            fn (ColonyClassResearch $colonyClassResearch): int => $colonyClassResearch->getResearch()->getId(),
            $this->colonyClassResearchRepository->getByColonyClass($colonyClass)
        );
        if ($researchIds !== [] && $this->researchedRepository->hasUserFinishedResearch($user, $researchIds) === false) {
            return false;
        }
        return true;
    }
}
