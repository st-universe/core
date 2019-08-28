<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAcademy;

use ColonyMenu;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowAcademy implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ACADEMY';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showAcademyRequest;

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

        $game->showMacro('html/colonymacros.xhtml/cm_academy');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_ACADEMY));
        $game->setTemplateVar('TRAINABLE_CREW_COUNT_PER_TICK', $trainableCrew);
    }
}
