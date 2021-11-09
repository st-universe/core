<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Stu\Lib\CleanTextUtils;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ClearFaultyBBCodes implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CORRUPT_BBCODES';

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $allShips = $this->shipRepository->findAll();

        foreach ($allShips as $ship) {
            if (!CleanTextUtils::checkBBCode($ship->getName())) {
                $game->addInformationf(_("ship_id: %d, name: %s"), $ship->getId(), $ship->getName());
            }
        }

        //$game->addInformation("Schiffsnamen wurde bereinigt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
