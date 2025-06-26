<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipRepair;

use Override;
use request;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;

final class ShowShipRepair implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_REPAIR';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private StationUtilityInterface $stationUtility,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

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

        $repairableShipWrappers = $station->getDockedShips()
            ->filter(fn(Ship $ship): bool => !$ship->getCondition()->isUnderRepair())
            ->map(fn(Ship $ship): ShipWrapperInterface => $this->spacecraftWrapperFactory->wrapShip($ship))
            ->filter(fn(ShipWrapperInterface $wrapper): bool => $wrapper->canBeRepaired());

        $game->appendNavigationPart(
            'station.php',
            'Stationen'
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
            'Schiffreparatur'
        );
        $game->setViewTemplate('html/station/shipRepair.twig');

        $game->setTemplateVar('REPAIRABLE_SHIP_WRAPPERS', $repairableShipWrappers);
        $game->setTemplateVar('STATION', $station);
    }
}
