<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAcademy;

use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ColonyMenu;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowAcademy implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ACADEMY';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowAcademyRequestInterface $showAcademyRequest;

    private CrewCountRetrieverInterface $crewCountRetriever;
    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowAcademyRequestInterface $showAcademyRequest,
        ColonyLibFactoryInterface $colonyLibFactory,
        CrewCountRetrieverInterface $crewCountRetriever
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showAcademyRequest = $showAcademyRequest;
        $this->crewCountRetriever = $crewCountRetriever;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showAcademyRequest->getColonyId(),
            $userId
        );

        $crewInTrainingCount = $this->crewCountRetriever->getInTrainingCount($user);
        $crewRemainingCount = $this->crewCountRetriever->getRemainingCount($user);
        $crewTrainableCount = $this->crewCountRetriever->getTrainableCount($user);

        $this->colonyGuiHelper->register($colony, $game);

        $trainableCrew = $crewTrainableCount - $crewInTrainingCount;
        if ($trainableCrew > $crewRemainingCount) {
            $trainableCrew = $crewRemainingCount;
        }
        if ($trainableCrew < 0) {
            $trainableCrew = 0;
        }
        if ($trainableCrew > $colony->getWorkless()) {
            $trainableCrew = $colony->getWorkless();
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony,
            $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction()
        )->getFreeAssignmentCount();

        if ($trainableCrew > $freeAssignmentCount) {
            $trainableCrew = $freeAssignmentCount;
        }

        $game->showMacro('html/colonymacros.xhtml/cm_academy');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_ACADEMY));
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
