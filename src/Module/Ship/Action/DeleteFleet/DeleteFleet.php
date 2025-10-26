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
    public const string ACTION_IDENTIFIER = 'B_DELETE_FLEET';

    public function __construct(private DeleteFleetRequestInterface $deleteFleetRequest, private FleetRepositoryInterface $fleetRepository, private ShipLoaderInterface $shipLoader, private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend) {}

    #[\Override]
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

        $game->getInfo()->addInformationWrapper($informations);

        foreach ($fleet->getShips() as $fleetShip) {
            $fleetShip->setFleet(null);
            $fleetShip->setIsFleetLeader(false);

            $this->shipLoader->save($fleetShip);
        }

        $this->fleetRepository->delete($fleet);

        $game->getInfo()->addInformation(_('Die Flotte wurde aufgelöst'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
