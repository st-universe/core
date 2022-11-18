<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowPodsLocations;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowPodsLocations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PODS_LOCATIONS';

    private ShipRepositoryInterface $shipRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipCrewRepositoryInterface $shipCrewRepository
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipCrewRepository = $shipCrewRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $pods = $this->shipRepository->getEscapePodsByCrewOwner($userId);
        $crewAssignmentsAtTradeposts = $this->shipCrewRepository->getByUserAtTradeposts($userId);

        $game->setPageTitle("Rettungskapsel/Tradeposts Scan");
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/orphanedcrewlocations');

        $game->setTemplateVar('PODS', $pods);
        $game->setTemplateVar('TRADEPOSTS', $crewAssignmentsAtTradeposts);
        $game->setTemplateVar('ERROR', false);
    }
}
