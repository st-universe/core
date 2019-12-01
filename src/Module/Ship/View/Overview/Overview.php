<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\Overview;

use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class Overview implements ViewControllerInterface
{
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
        $userId = $game->getUser()->getId();

        $fleets = $this->fleetRepository->getByUser($userId);
        $bases = $this->shipRepository->getByUserAndFleetAndBase($userId, null, true);
        $ships = $this->shipRepository->getByUserAndFleetAndBase($userId, null, false);

        $game->appendNavigationPart(
            'ship.php',
            _('Schiffe')
        );
        $game->setPageTitle(_('/ Schiffe'));
        $game->setTemplateFile('html/shiplist.xhtml');

        $game->setTemplateVar('MAX_POINTS_PER_FLEET', GameEnum::POINTS_PER_FLEET);
        $game->setTemplateVar(
            'SHIPS_AVAILABLE',
            $fleets !== [] || $ships !== [] || $bases !== []
        );
        $game->setTemplateVar(
            'FLEETS',
            $fleets
        );
        $game->setTemplateVar(
            'BASES',
            $bases
        );
        $game->setTemplateVar(
            'SHIPS',
            $ships
        );
    }
}
