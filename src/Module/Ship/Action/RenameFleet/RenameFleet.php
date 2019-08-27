<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\RenameFleet;

use Fleet;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class RenameFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_CHANGE_NAME';

    private $renameFleetRequest;

    public function __construct(
        RenameFleetRequestInterface $renameFleetRequest
    ) {
        $this->renameFleetRequest = $renameFleetRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $newName = $this->renameFleetRequest->getNewName();
        if (mb_strlen($newName) === 0) {
            return;
        }

        $fleet = Fleet::getUserFleetById($this->renameFleetRequest->getFleetId(), $game->getUser()->getId());
        $fleet->setName($newName);
        $fleet->save();

        $game->addInformation(_('Der Name der Flotte wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
