<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAcademy;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowAcademy implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ACADEMY';

    private ColonyLoaderInterface $colonyLoader;

    private ShowAcademyRequestInterface $showAcademyRequest;

    private CrewCountRetrieverInterface $crewCountRetriever;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowAcademyRequestInterface $showAcademyRequest,
        ColonyLibFactoryInterface $colonyLibFactory,
        CrewCountRetrieverInterface $crewCountRetriever
    ) {
        $this->colonyLoader = $colonyLoader;
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
            $userId,
            false
        );

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
        if ($trainableCrew > $colony->getWorkless()) {
            $trainableCrew = $colony->getWorkless();
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony
        )->getFreeAssignmentCount();

        if ($trainableCrew > $freeAssignmentCount) {
            $trainableCrew = $freeAssignmentCount;
        }

        $game->showMacro(ColonyMenuEnum::MENU_ACADEMY->getTemplate());
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_ACADEMY);

        $game->setTemplateVar('HOST', $colony);
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
