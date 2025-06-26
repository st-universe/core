<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\StationRepositoryInterface;

final class StationProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private StationRepositoryInterface $stationRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $stations = $this->stationRepository->getByUser($userId);
        $uplinkStations = $this->stationRepository->getByUplink($userId);

        $game->setTemplateVar(
            'STATIONS',
            array_map(
                fn(Station $station): StationWrapperInterface => $this->spacecraftWrapperFactory->wrapStation($station),
                array_merge($stations, $uplinkStations)
            )
        );
    }
}
