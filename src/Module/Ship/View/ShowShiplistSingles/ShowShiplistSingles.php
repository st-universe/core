<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShiplistSingles;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowShiplistSingles implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPLIST_SINGLES';

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ships = $this->shipRepository->getByUserAndFleetAndBase($userId, null, false);

        $game->setTemplateVar('SHIPS', $ships);
        $game->showMacro('html/shipmacros.xhtml/shiplist_singles');
    }
}
