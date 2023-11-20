<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAirfield;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShowAirfield implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_AIRFIELD';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowAirfieldRequestInterface $showAirfieldRequest;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowAirfieldRequestInterface $showAirfieldRequest,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showAirfieldRequest = $showAirfieldRequest;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showAirfieldRequest->getColonyId(),
            $userId,
            false
        );

        $this->colonyGuiHelper->registerComponents($colony, $game);
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_AIRFIELD);

        $game->showMacro(ColonyMenuEnum::MENU_AIRFIELD->getTemplate());

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar(
            'STARTABLE_SHIPS',
            $this->shipRumpRepository->getStartableByColony($colony->getId())
        );
        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            $this->shipRumpRepository->getBuildableByUserAndBuildingFunction(
                $userId,
                BuildingEnum::BUILDING_FUNCTION_AIRFIELD
            )
        );
    }
}
