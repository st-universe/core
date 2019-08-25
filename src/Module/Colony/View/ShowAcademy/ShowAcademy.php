<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAcademy;

use ColonyMenu;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowAcademy implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ACADEMY';

    private $colonyLoader;

    private $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
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

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/cm_academy');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_ACADEMY));
        $game->setTemplateVar('TRAINABLE_CREW_COUNT_PER_TICK', $trainableCrew);
    }
}
