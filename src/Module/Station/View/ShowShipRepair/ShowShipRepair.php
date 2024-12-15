<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipRepair;

use Override;
use request;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class ShowShipRepair implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_REPAIR';

    public function __construct(private StationLoaderInterface $stationLoader, private StationUtilityInterface $stationUtility, private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->stationLoader->getByIdAndUser(
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
            $wrapper = $this->spacecraftWrapperFactory->wrapShip($ship);
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
                ShowSpacecraft::VIEW_IDENTIFIER,
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
