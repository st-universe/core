<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipManagement;

use Override;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class ShowShipManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_MANAGEMENT';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private ShowShipManagementRequestInterface $showShipManagementRequest,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private StationUtilityInterface $stationUtility
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->stationLoader->getByIdAndUser(
            $this->showShipManagementRequest->getStationId(),
            $userId,
            false,
            false
        );

        if (!$this->stationUtility->canManageShips($station)) {
            return;
        }

        $dockedShips = $station->getDockedShips();
        $groups = $this->spacecraftWrapperFactory->wrapSpacecraftsAsGroups($dockedShips);

        $game->appendNavigationPart(
            'station.php',
            _('Stationen')
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%s',
                ShowSpacecraft::VIEW_IDENTIFIER,
                $station->getId()
            ),
            $station->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%d',
                self::VIEW_IDENTIFIER,
                $station->getId()
            ),
            _('Schiffsmanagement')
        );
        $game->setPagetitle(sprintf('%s Bereich', $station->getName()));
        $game->setViewTemplate('html/station/shipManagement.twig');

        $game->setTemplateVar('MANAGER_ID', $station->getId());
        $game->setTemplateVar('SPACECRAFT_GROUPS', $groups);
    }
}
