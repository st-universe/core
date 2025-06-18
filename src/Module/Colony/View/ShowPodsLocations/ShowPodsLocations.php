<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowPodsLocations;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowPodsLocations implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PODS_LOCATIONS';

    public function __construct(private ShipRepositoryInterface $shipRepository, private CrewAssignmentRepositoryInterface $shipCrewRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $pods = $this->shipRepository->getEscapePodsByCrewOwner($userId);
        $crewAssignmentsAtTradeposts = $this->shipCrewRepository->getOrphanedSummaryByUserAtTradeposts($userId);

        $game->setPageTitle("Rettungskapsel/Tradeposts Scan");
        $game->setMacroInAjaxWindow('html/colony/component/orphanedcrewlocations.twig');

        $game->setTemplateVar('PODS', $pods);
        $game->setTemplateVar('TRADEPOSTS', $crewAssignmentsAtTradeposts);
    }
}
