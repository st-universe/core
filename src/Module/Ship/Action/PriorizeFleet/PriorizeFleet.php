<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\PriorizeFleet;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class PriorizeFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FLEET_UP';

    public function __construct(private PriorizeFleetRequestInterface $priorizeFleetRequest, private FleetRepositoryInterface $fleetRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $fleet = $this->fleetRepository->find($this->priorizeFleetRequest->getFleetId());
        if ($fleet === null || $fleet->getUser()->getId() !== $game->getUser()->getId()) {
            throw new AccessViolationException();
        }

        $fleet->setSort($this->fleetRepository->getHighestSortByUser($game->getUser()->getId()));

        $this->fleetRepository->save($fleet);

        $game->getInfo()->addInformation(_('Die Flotte wurde nach oben sortiert'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
