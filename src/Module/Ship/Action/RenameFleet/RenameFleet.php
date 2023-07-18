<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\RenameFleet;

use Stu\Exception\AccessViolation;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class RenameFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_CHANGE_NAME';

    private RenameFleetRequestInterface $renameFleetRequest;

    private FleetRepositoryInterface $fleetRepository;

    public function __construct(
        RenameFleetRequestInterface $renameFleetRequest,
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->renameFleetRequest = $renameFleetRequest;
        $this->fleetRepository = $fleetRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $text = $this->renameFleetRequest->getNewName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $game->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $newName = CleanTextUtils::clearEmojis($text);
        if (mb_strlen($newName) === 0) {
            return;
        }

        $nameWithoutUnicode = CleanTextUtils::clearUnicode($newName);
        if ($newName !== $nameWithoutUnicode) {
            $game->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($newName) > 200) {
            $game->addInformation(_('Der Name ist zu lang (Maximum: 200 Zeichen)'));
            return;
        }

        $fleet = $this->fleetRepository->find($this->renameFleetRequest->getFleetId());

        if ($fleet === null || $fleet->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
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
