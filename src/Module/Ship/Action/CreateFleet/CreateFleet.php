<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class CreateFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_NEW_FLEET';

    public function __construct(private CreateFleetRequestInterface $createFleetRequest, private FleetRepositoryInterface $fleetRepository, private ShipLoaderInterface $shipLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser($this->createFleetRequest->getShipId(), $game->getUser()->getId());

        if ($ship->getFleetId()) {
            return;
        }
        if ($ship->isBase()) {
            return;
        }
        if ($ship->isTractored()) {
            $game->addInformation(
                _('Aktion nicht möglich, da Schiff von einem Traktorstrahl gehalten wird.'),
            );
            return;
        }
        if ($ship->getTakeoverPassive() !== null) {
            $game->addInformation(
                _('Aktion nicht möglich, da Schiff im Begriff ist übernommen zu werden.'),
            );
            return;
        }

        $fleet = $this->fleetRepository->prototype();
        $fleet->setLeadShip($ship);
        $fleet->setUser($game->getUser());
        $fleet->setName(_('Flotte'));
        $fleet->setSort($this->fleetRepository->getHighestSortByUser($game->getUser()->getId()));
        $fleet->setIsFleetFixed($game->getUser()->getFleetFixedDefault());

        $fleet->getShips()->add($ship);

        $this->fleetRepository->save($fleet);

        $ship->setFleet($fleet);
        $ship->setIsFleetLeader(true);

        $this->shipLoader->save($ship);

        $game->addInformation(_('Die Flotte wurde erstellt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
