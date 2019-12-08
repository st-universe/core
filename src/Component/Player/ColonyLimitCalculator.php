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

    public function canColonizeFurtherPlanets(UserInterface $user): bool
    {
        return $this->getPlanetColonyCount($user) < $this->getPlanetColonyLimit($user);
    }

    public function canColonizeFurtherMoons(UserInterface $user): bool
    {
        return $this->getMoonColonyCount($user) < $this->getMoonColonyLimit($user);
    }

    public function getPlanetColonyLimit(UserInterface $user): int
    {
        return $this->researchRepository->getPlanetColonyLimitByUser($user);
    }

    public function getMoonColonyLimit(UserInterface $user): int
    {
        return $this->researchRepository->getMoonColonyLimitByUser($user);
    }

    public function getPlanetColonyCount(UserInterface $user): int
    {
        return $this->colonyRepository->getAmountByUser($user);
    }

    public function getMoonColonyCount(UserInterface $user): int
    {
        return $this->colonyRepository->getAmountByUser($user, true);
    }
}
