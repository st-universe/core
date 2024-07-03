<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Component\Building\BuildingEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class AirfieldProvider implements GuiComponentProviderInterface
{
    public function __construct(private ShipRumpRepositoryInterface $shipRumpRepository)
    {
    }

    #[Override]
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
