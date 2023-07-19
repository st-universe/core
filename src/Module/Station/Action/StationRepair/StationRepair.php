<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\StationRepair;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class StationRepair implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_STATION_REPAIR';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(request::postIntFatal('id'), $userId);

        if (!$station->hasEnoughCrew($game)) {
            return;
        }

        if (!$station->isAlertGreen()) {
            return;
        }

        if ($station->isUnderRepair()) {
            $game->addInformation(_('Die Station wird bereits repariert.'));
            return;
        }

        $station->setState(ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE);

        $this->shipRepository->save($station);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
