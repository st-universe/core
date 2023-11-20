<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSocial;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowSocial implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SOCIAL';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private CrewCountRetrieverInterface $crewCountRetriever;

    private CrewLimitCalculatorInterface $crewLimitCalculator;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        ColonyGuiHelperInterface $colonyGuiHelper,
        CrewLimitCalculatorInterface $crewLimitCalculator,
        CrewCountRetrieverInterface $crewCountRetriever
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->crewCountRetriever = $crewCountRetriever;
        $this->crewLimitCalculator = $crewLimitCalculator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser());

        $this->colonyGuiHelper->registerComponents($host, $game);

        $game->showMacro(ColonyMenuEnum::MENU_SOCIAL->getTemplate());
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_SOCIAL);

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
