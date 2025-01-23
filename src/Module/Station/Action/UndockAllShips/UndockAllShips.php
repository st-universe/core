<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\UndockAllShips;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Station\Lib\StationLoaderInterface;

final class UndockAllShips implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNDOCK_ALL_SHIPS';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private ShipUndockingInterface $shipUndocking
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $stationId = request::indInt('id');

        $station = $this->stationLoader->getByIdAndUser($stationId, $game->getUser()->getId());

        $dockedShipsCount = $station->getDockedShipCount();
        $this->shipUndocking->undockAllDocked($station);

        $game->addInformationf('Alle %d Schiffe wurden erfolgreich abgedockt', $dockedShipsCount);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
