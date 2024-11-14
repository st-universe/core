<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipRepair;

use Override;
use request;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class ShowShipRepair implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_REPAIR';

    public function __construct(private ShipLoaderInterface $shipLoader, private StationUtilityInterface $stationUtility, private ShipWrapperFactoryInterface $shipWrapperFactory)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        if (!$this->stationUtility->canRepairShips($station)) {
            return;
        }

        $repairableShips = [];
        foreach ($station->getDockedShips() as $ship) {
            $wrapper = $this->shipWrapperFactory->wrapShip($ship);
            if (
                !$wrapper->canBeRepaired() || $ship->isUnderRepair()
            ) {
                continue;
            }
            $repairableShips[$ship->getId()] = $wrapper;
        }

        $game->appendNavigationPart(
            'station.php',
            _('Stationen')
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%d',
                ShowShip::VIEW_IDENTIFIER,
                $station->getId()
            ),
            $station->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%d',
                self::VIEW_IDENTIFIER,
                $station->getId()
            ),
            _('Schiffreparatur')
        );
        $game->setViewTemplate('html/station/shipRepair.twig');

        $game->setTemplateVar('REPAIRABLE_SHIP_LIST', $repairableShips);
        $game->setTemplateVar('STATION', $station);
    }
}
