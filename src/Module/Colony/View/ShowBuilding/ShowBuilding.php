<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuilding;

use Building;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBuilding implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDING';

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
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $building = new Building(request::getIntFatal('bid'));

        $game->setTemplateVar('buildingdata', $building);
        $game->setPageTitle($building->getName());
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/buildinginfo');
        $game->setTemplateVar('COLONY', $colony);
    }
}
