<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;

final class SocialProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private CrewLimitCalculatorInterface $crewLimitCalculator, private CrewCountRetrieverInterface $crewCountRetriever, private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {
        $user = $game->getUser();

        $game->setTemplateVar(
            'POPULATION_CALCULATOR',
            $this->colonyLibFactory->createColonyPopulationCalculator($entity)
        );

        $game->setTemplateVar('FACTION', $game->getUser()->getFaction());

        $game->setTemplateVar(
            'CREW_COUNT_DEBRIS_AND_TRADE_POSTS',
            $this->crewCountRetriever->getDebrisAndTradePostsCount($user)
        );
        $game->setTemplateVar(
            'CREW_COUNT_SHIPS',
            $this->crewCountRetriever->getAssignedToShipsCount($user)
        );
        $game->setTemplateVar(
            'CREW_COUNT_TRAINING',
            $this->crewCountRetriever->getInTrainingCount($user)
        );
        $game->setTemplateVar(
            'CREW_COUNT_REMAINING',
            $this->crewCountRetriever->getRemainingCount($user)
        );
        $game->setTemplateVar(
            'GLOBAL_CREW_LIMIT',
            $this->crewLimitCalculator->getGlobalCrewLimit($user)
        );
    }
}
