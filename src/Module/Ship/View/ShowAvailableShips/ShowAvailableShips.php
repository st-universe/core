<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAvailableShips;

use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowAvailableShips implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_AVAILABLE_SHIPS';

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $fleet = $this->fleetRepository->find(request::getIntFatal('fleetid'));

        if ($fleet === null || $fleet->getUserId() !== $user->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Schiff hinzufügen'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_available_ships');

        $game->setTemplateVar('FLEET', $fleet);
        $game->setTemplateVar('AVAILABLE_SHIPS', $this->shipRepository->getPossibleFleetMembers($fleet->getLeadShip()));
    }
}
