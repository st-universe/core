<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;

final class AcademyProvider implements GuiComponentProviderInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory, private CrewCountRetrieverInterface $crewCountRetriever)
    {
    }

    /** @param ColonyInterface&PlanetFieldHostInterface $host */
    #[Override]
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $user = $game->getUser();

        $crewInTrainingCount = $this->crewCountRetriever->getInTrainingCount($user);
        $crewRemainingCount = $this->crewCountRetriever->getRemainingCount($user);
        $crewTrainableCount = $this->crewCountRetriever->getTrainableCount($user);

        $trainableCrew = $crewTrainableCount - $crewInTrainingCount;
        if ($trainableCrew > $crewRemainingCount) {
            $trainableCrew = $crewRemainingCount;
        }
        if ($trainableCrew < 0) {
            $trainableCrew = 0;
        }
        if ($trainableCrew > $host->getWorkless()) {
            $trainableCrew = $host->getWorkless();
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $host
        )->getFreeAssignmentCount();

        if ($trainableCrew > $freeAssignmentCount) {
            $trainableCrew = $freeAssignmentCount;
        }

        $game->setTemplateVar('TRAINABLE_CREW_COUNT_PER_TICK', $trainableCrew);
        $game->setTemplateVar(
            'CREW_COUNT_TRAINING',
            $crewInTrainingCount
        );
        $game->setTemplateVar(
            'CREW_COUNT_REMAINING',
            $crewRemainingCount
        );
        $game->setTemplateVar(
            'CREW_COUNT_TRAINABLE',
            $crewTrainableCount
        );
    }
}
