<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\RenameFleet;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class RenameFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_CHANGE_NAME';

    private $renameFleetRequest;

    private $fleetRepository;

    public function __construct(
        RenameFleetRequestInterface $renameFleetRequest,
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->renameFleetRequest = $renameFleetRequest;
        $this->fleetRepository = $fleetRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $newName = $this->renameFleetRequest->getNewName();
        if (mb_strlen($newName) === 0) {
            return;
        }

        $fleet = $this->fleetRepository->find($this->renameFleetRequest->getFleetId());

        if ($fleet === null || $fleet->getUserId() !== $game->getUser()->getId()) {
            throw new \AccessViolation();
        }

        $fleet->setName($newName);

        $this->fleetRepository->save($fleet);

        $game->addInformation(_('Der Name der Flotte wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
