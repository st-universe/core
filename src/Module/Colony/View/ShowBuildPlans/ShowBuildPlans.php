<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildPlans;

use ColonyMenu;
use ShipBuildplans;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;

final class ShowBuildPlans implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDPLANS';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showBuildPlansRequest;

    private $buildingFunctionRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowBuildPlansRequestInterface $showBuildPlansRequest,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showBuildPlansRequest = $showBuildPlansRequest;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildPlansRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $buildingFunction = $this->buildingFunctionRepository->find(
            $this->showBuildPlansRequest->getBuildingFunctionId()
        );

        $game->showMacro('html/colonymacros.xhtml/cm_buildplans');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_BUILDPLANS));
        $game->setTemplateVar(
            'AVAILABLE_BUILDPLANS',
            ShipBuildplans::getBuildplansByUserAndFunction($userId, $buildingFunction->getFunction())
        );
    }
}
