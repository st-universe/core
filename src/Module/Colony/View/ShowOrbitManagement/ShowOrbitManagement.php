<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitManagement;

use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowOrbitManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ORBITAL_SHIPS';

    private ColonyLoaderInterface $colonyLoader;

    private ShowOrbitManagementRequestInterface $showOrbitManagementRequest;

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowOrbitManagementRequestInterface $showOrbitManagementRequest,
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showOrbitManagementRequest = $showOrbitManagementRequest;
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showOrbitManagementRequest->getColonyId(),
            $userId,
            false
        );

        $shipList = $this->shipRepository->getByLocation(
            $colony->getStarsystemMap(),
            null
        );

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
                static::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            _('Orbitalmanagement')
        );
        $game->setPagetitle(sprintf('%s Orbit', $colony->getName()));
        $game->setTemplateFile('html/orbitalmanagement.xhtml');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('FLEETWRAPPERS', $list);
    }
}
