<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowPodsLocations;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowPodsLocations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PODS_LOCATIONS';

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $pods = $this->shipRepository->getEscapePodsByCrewOwner($userId);

        $game->setPageTitle("Rettungskapsel Scan");
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/podlocations');

        $game->setTemplateVar('PODS', $pods);
        $game->setTemplateVar('ERROR', false);
    }
}
