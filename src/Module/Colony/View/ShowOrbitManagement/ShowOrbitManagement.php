<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitManagement;

use Override;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowOrbitManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_MANAGEMENT';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ShowOrbitManagementRequestInterface $showOrbitManagementRequest, private ShipRepositoryInterface $shipRepository, private ShipWrapperFactoryInterface $shipWrapperFactory)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showOrbitManagementRequest->getColonyId(),
            $userId,
            false
        );

        $shipList = $this->shipRepository->getByLocation($colony->getStarsystemMap());

        $groupedList = [];

        foreach ($shipList as $ship) {
            $fleet = $ship->getFleet();
            $fleetId = $fleet === null ? 0 : $fleet->getId();

            $fleet = $groupedList[$fleetId] ?? null;
            if ($fleet === null) {
                $groupedList[$fleetId] = [];
            }

            $groupedList[$fleetId][] = $ship;
        }

        $list = [];

        foreach ($groupedList as $fleetId => $shipList) {
            $fleetWrapper = $this->shipWrapperFactory->wrapShipsAsFleet($shipList, $fleetId === 0);
            $key = sprintf('%d.%d', $fleetWrapper->get()->getSort(), $fleetWrapper->get()->getUser()->getId());
            $list[$key] = $fleetWrapper;
        }

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%d',
                self::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            _('Orbitalmanagement')
        );
        $game->setPagetitle(sprintf('%s Orbit', $colony->getName()));
        $game->setViewTemplate('html/colony/menu/orbitalmanagement.twig');

        $game->setTemplateVar('MANAGER_ID', $colony->getId());
        $game->setTemplateVar('FLEET_WRAPPERS', $list);
    }
}
