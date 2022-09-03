<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShiplistFleet;

use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class ShowShiplistFleet implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPLIST_FLEET';

    private FleetRepositoryInterface $fleetRepository;

    public function __construct(
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->fleetRepository = $fleetRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $fleetId = request::getIntFatal('fleetid');

        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null) {
            return;
        }

        if ($fleet->getUser() !== $game->getUser()) {
            throw new AccessViolation(sprintf('tried to refresh foreign fleet, idiot: %d', $userId));
        }

        $game->setTemplateVar('fleet', $fleet);
        $game->showMacro('html/shipmacros.xhtml/shiplist_fleetform');
    }
}
