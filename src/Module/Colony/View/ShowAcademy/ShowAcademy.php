<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAcademy;

use ColonyMenu;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowAcademy implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ACADEMY';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowAcademyRequestInterface $showAcademyRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowAcademyRequestInterface $showAcademyRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showAcademyRequest = $showAcademyRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showAcademyRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $trainableCrew = $user->getTrainableCrewCountMax() - $user->getInTrainingCrewCount();
        if ($trainableCrew > $user->getCrewLeftCount()) {
            $trainableCrew = $user->getCrewLeftCount();
        }
        if ($trainableCrew < 0) {
            $trainableCrew = 0;
        }
        if ($trainableCrew > $colony->getWorkless()) {
            $trainableCrew = $colony->getWorkless();
        }
        if ($trainableCrew > $colony->getFreeAssignmentCount()) {
            $trainableCrew = $colony->getFreeAssignmentCount();
        }

        $game->showMacro('html/colonymacros.xhtml/cm_academy');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_ACADEMY));
        $game->setTemplateVar('TRAINABLE_CREW_COUNT_PER_TICK', $trainableCrew);
    }
}
