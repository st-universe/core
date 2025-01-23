<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class CreateFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_NEW_FLEET';

    public function __construct(
        private CreateFleetRequestInterface $createFleetRequest,
        private FleetRepositoryInterface $fleetRepository,
        private ShipLoaderInterface $shipLoader
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $spacecraft = $this->shipLoader->getByIdAndUser($this->createFleetRequest->getShipId(), $game->getUser()->getId());

        if ($spacecraft->getFleetId()) {
            return;
        }
        if ($spacecraft->isUnderRetrofit()) {
            $game->addInformation(_('Aktion nicht möglich, da das Schiff umgerüstet wird.'));
            return;
        }
        if ($spacecraft->isTractored()) {
            $game->addInformation(
                _('Aktion nicht möglich, da Schiff von einem Traktorstrahl gehalten wird.'),
            );
            return;
        }

        if ($spacecraft->getTakeoverPassive() !== null) {
            $game->addInformation(
                _('Aktion nicht möglich, da Schiff im Begriff ist übernommen zu werden.'),
            );
            return;
        }

        $fleet = $this->fleetRepository->prototype();
        $fleet->setLeadShip($spacecraft);
        $fleet->setUser($game->getUser());
        $fleet->setName(_('Flotte'));
        $fleet->setSort($this->fleetRepository->getHighestSortByUser($game->getUser()->getId()));
        $fleet->setIsFleetFixed($game->getUser()->getFleetFixedDefault());

        $fleet->getShips()->add($spacecraft);

        $this->fleetRepository->save($fleet);

        $spacecraft->setFleet($fleet);
        $spacecraft->setIsFleetLeader(true);

        $this->shipLoader->save($spacecraft);

        $game->addInformation(_('Die Flotte wurde erstellt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
