<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\UserInterface;

final class CrewLimitCalculator implements CrewLimitCalculatorInterface
{
    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function getGlobalCrewLimit(UserInterface $user): int
    {
        $limit = 0;
        foreach ($user->getColonies() as $colony) {
            $limit += $this->colonyLibFactory->createColonyPopulationCalculator(
                $colony,
                $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction()
            )->getCrewLimit();
        }
        return $limit;
    }
}
