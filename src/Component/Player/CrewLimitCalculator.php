<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Override;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\UserInterface;

final class CrewLimitCalculator implements CrewLimitCalculatorInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory)
    {
    }

    #[Override]
    public function getGlobalCrewLimit(UserInterface $user): int
    {
        $limit = 0;
        foreach ($user->getColonies() as $colony) {
            $limit += $this->colonyLibFactory->createColonyPopulationCalculator($colony)->getCrewLimit();
        }
        return $limit;
    }
}
