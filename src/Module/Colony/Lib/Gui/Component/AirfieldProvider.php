<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Component\Building\BuildingEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class AirfieldProvider implements GuiComponentProviderInterface
{
    private ShipRumpRepositoryInterface $shipRumpRepository;

    public function __construct(
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {

        $game->setTemplateVar(
            'STARTABLE_SHIPS',
            $this->shipRumpRepository->getStartableByColony($host->getId())
        );
        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            $this->shipRumpRepository->getBuildableByUserAndBuildingFunction(
                $game->getUser()->getId(),
                BuildingEnum::BUILDING_FUNCTION_AIRFIELD
            )
        );
    }
}
