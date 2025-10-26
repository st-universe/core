<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationShiplist;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Entity\Ship;

final class ShowStationShiplist implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_STATION_SHIPLIST';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private ShowStationShiplistRequestInterface $showStationShiplistRequest,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            $this->showStationShiplistRequest->getStationId(),
            $userId,
            false,
            false
        );

        $shipList = $wrapper->get()->getDockedShips()
            ->map(fn(Ship $ship): ShipWrapperInterface => $this->spacecraftWrapperFactory->wrapShip($ship));

        $game->setPageTitle(_('Angedockte Schiffe'));
        $game->setMacroInAjaxWindow('html/station/shipList.twig');
        $game->setTemplateVar('STATION', $wrapper->get());
        $game->setTemplateVar('WRAPPERS', $shipList);
    }
}
