<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetTypeResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PlanetTypeResearchRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class ColonizationChecker implements ColonizationCheckerInterface
{
    private ResearchedRepositoryInterface $researchedRepository;

    private PlanetTypeResearchRepositoryInterface $planetTypeResearchRepository;

    private ColonyLimitCalculatorInterface $colonyLimitCalculator;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        PlanetTypeResearchRepositoryInterface $planetTypeResearchRepository,
        ColonyLimitCalculatorInterface $colonyLimitCalculator
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->planetTypeResearchRepository = $planetTypeResearchRepository;
        $this->colonyLimitCalculator = $colonyLimitCalculator;
    }

    public function canColonize(UserInterface $user, ColonyInterface $colony): bool
    {
        if ($user->getId() === 126) {
            return true;
        }
        if ($colony->isFree() === false) {
            return false;
        }

        $planetType = $colony->getPlanetType();

        if ($planetType->getIsMoon()) {
            if ($this->colonyLimitCalculator->canColonizeFurtherMoons($user) === false) {
                return false;
            }
        } else {
            if ($this->colonyLimitCalculator->canColonizeFurtherPlanets($user) === false) {
                return false;
            }
        }

        $researchIds = array_map(
            function (PlanetTypeResearchInterface $planetTypeResearch): int {
                return $planetTypeResearch->getResearch()->getId();
            },
            $this->planetTypeResearchRepository->getByPlanetType($planetType)
        );
        if ($researchIds !== [] && $this->researchedRepository->hasUserFinishedResearch($user, $researchIds) === false) {
            return false;
        }
        return true;
    }
}
