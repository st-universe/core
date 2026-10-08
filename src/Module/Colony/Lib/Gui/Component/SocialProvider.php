<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

final class SocialProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private CrewLimitCalculatorInterface $crewLimitCalculator, private CrewCountRetrieverInterface $crewCountRetriever, private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {
        $template->setTemplateVar(
            'POPULATION_CALCULATOR',
            $this->colonyLibFactory->createColonyPopulationCalculator($entity)
        );

        $template->setTemplateVar('FACTION', $user->getFaction());

        $template->setTemplateVar(
            'CREW_COUNT_DEBRIS_AND_TRADE_POSTS',
            $this->crewCountRetriever->getDebrisAndTradePostsCount($user)
        );
        $template->setTemplateVar(
            'CREW_COUNT_SHIPS',
            $this->crewCountRetriever->getAssignedToShipsCount($user)
        );
        $template->setTemplateVar(
            'CREW_COUNT_TRAINING',
            $this->crewCountRetriever->getInTrainingCount($user)
        );
        $template->setTemplateVar(
            'CREW_COUNT_REMAINING',
            $this->crewCountRetriever->getRemainingCount($user)
        );
        $template->setTemplateVar(
            'GLOBAL_CREW_LIMIT',
            $this->crewLimitCalculator->getGlobalCrewLimit($user)
        );
    }
}
