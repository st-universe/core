<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationShiplist;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;

final class ShowStationShiplist implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_STATION_SHIPLIST';

    public function __construct(private ShipLoaderInterface $shipLoader, private ShowStationShiplistRequestInterface $showStationShiplistRequest, private ShipWrapperFactoryInterface $shipWrapperFactory)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            $this->showStationShiplistRequest->getStationId(),
            $userId,
            false,
            false
        );

        if (!$station->isBase()) {
            return;
        }

        $shipList = $this->shipWrapperFactory->wrapShips($station->getDockedShips()->toArray());

        $game->setPageTitle(_('Angedockte Schiffe'));
        $game->setMacroInAjaxWindow('html/stationmacros.xhtml/shiplist');
        $game->setTemplateVar('SHIP', $station);
        $game->setTemplateVar('WRAPPERS', $shipList);
        $game->setTemplateVar('CAN_UNDOCK', true);
    }
}
