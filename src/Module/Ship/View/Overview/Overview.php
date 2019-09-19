<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\Overview;

use Ship;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private $fleetRepository;

    public function __construct(
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->fleetRepository = $fleetRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $fleets = $this->fleetRepository->getByUser($userId);
        $bases = Ship::getObjectsBy("WHERE user_id=" . $userId . " AND fleets_id=0 AND is_base=1 ORDER BY id");
        $ships = Ship::getObjectsBy("WHERE user_id=" . $userId . " AND fleets_id=0 AND is_base=0 ORDER BY id");

        $game->appendNavigationPart(
            'ship.php',
            _('Schiffe')
        );
        $game->setPageTitle(_('/ Schiffe'));
        $game->setTemplateFile('html/shiplist.xhtml');

        $game->setTemplateVar('MAX_POINTS_PER_FLEET', POINTS_PER_FLEET);
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
