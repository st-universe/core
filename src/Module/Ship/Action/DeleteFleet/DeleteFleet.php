<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeleteFleet;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class DeleteFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_FLEET';

    private DeleteFleetRequestInterface $deleteFleetRequest;

    private FleetRepositoryInterface $fleetRepository;

    private ShipLoaderInterface $shipLoader;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    public function __construct(
        DeleteFleetRequestInterface $deleteFleetRequest,
        FleetRepositoryInterface $fleetRepository,
        ShipLoaderInterface $shipLoader,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend
    ) {
        $this->deleteFleetRequest = $deleteFleetRequest;
        $this->fleetRepository = $fleetRepository;
        $this->shipLoader = $shipLoader;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser($this->deleteFleetRequest->getShipId(), $game->getUser()->getId());

        $fleet = $ship->getFleet();
        if ($fleet === null) {
            return;
        }
        if (!$ship->isFleetLeader()) {
            return;
        }


        $informations = new InformationWrapper();
        $this->cancelColonyBlockOrDefend->work($ship, $informations);

        $game->addInformationWrapper($informations);

        foreach ($fleet->getShips() as $fleetShip) {
            $fleetShip->setFleet(null);
            $fleetShip->setIsFleetLeader(false);

            $this->shipLoader->save($fleetShip);
        }

        $this->fleetRepository->delete($fleet);

        $game->addInformation(_('Die Flotte wurde aufgelöst'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
