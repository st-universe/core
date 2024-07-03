<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Override;
use Stu\Orm\Entity\ColonyClassResearchInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyClassResearchRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class ColonizationChecker implements ColonizationCheckerInterface
{
    public function __construct(private ResearchedRepositoryInterface $researchedRepository, private ColonyClassResearchRepositoryInterface $colonyClassResearchRepository, private ColonyLimitCalculatorInterface $colonyLimitCalculator)
    {
    }

    #[Override]
    public function canColonize(UserInterface $user, ColonyInterface $colony): bool
    {
        if (!$colony->isFree()) {
            return false;
        }

        $colonyClass = $colony->getColonyClass();

        if (!$this->colonyLimitCalculator->canColonizeFurtherColonyWithType($user, $colonyClass->getType())) {
            return false;
        }

        $researchIds = array_map(
            fn (ColonyClassResearchInterface $colonyClassResearch): int => $colonyClassResearch->getResearch()->getId(),
            $this->colonyClassResearchRepository->getByColonyClass($colonyClass)
        );
        if ($researchIds !== [] && $this->researchedRepository->hasUserFinishedResearch($user, $researchIds) === false) {
            return false;
        }
        return true;
    }
}
