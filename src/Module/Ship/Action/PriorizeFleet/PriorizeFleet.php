<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\PriorizeFleet;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class PriorizeFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_UP';

    private PriorizeFleetRequestInterface $priorizeFleetRequest;

    private FleetRepositoryInterface $fleetRepository;

    public function __construct(
        PriorizeFleetRequestInterface $priorizeFleetRequest,
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->priorizeFleetRequest = $priorizeFleetRequest;
        $this->fleetRepository = $fleetRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $fleet = $this->fleetRepository->find($this->priorizeFleetRequest->getFleetId());
        if ($fleet === null || $fleet->getUser() !== $game->getUser()) {
            throw new AccessViolation();
        }

        $fleet->setSort($this->fleetRepository->getHighestSortByUser($game->getUser()->getId()));

        $this->fleetRepository->save($fleet);

        $game->addInformation(_('Die Flotte wurde nach oben sortiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
