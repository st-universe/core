<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSocial;

use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ColonyMenu;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowSocial implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SOCIAL';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowSocialRequestInterface $showSocialRequest;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CrewCountRetrieverInterface $crewCountRetriever;

    private CrewLimitCalculatorInterface $crewLimitCalculator;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowSocialRequestInterface $showSocialRequest,
        ColonyLibFactoryInterface $colonyLibFactory,
        CrewLimitCalculatorInterface $crewLimitCalculator,
        CrewCountRetrieverInterface $crewCountRetriever
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showSocialRequest = $showSocialRequest;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->crewCountRetriever = $crewCountRetriever;
        $this->crewLimitCalculator = $crewLimitCalculator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showSocialRequest->getColonyId(),
            $user->getId(),
            false
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/cm_social');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_SOCIAL));
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
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
            'SHIELDING_MANAGER',
            $this->colonyLibFactory->createColonyShieldingManager($colony)
        );
        $game->setTemplateVar(
            'GLOBAL_CREW_LIMIT',
            $this->crewLimitCalculator->getGlobalCrewLimit($user)
        );
    }
}
