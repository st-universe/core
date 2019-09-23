<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitManagement;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowOrbitManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ORBITAL_SHIPS';

    private $colonyLoader;

    private $showOrbitManagementRequest;

    private $colonyLibFactory;

    private $shipRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowOrbitManagementRequestInterface $showOrbitManagementRequest,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showOrbitManagementRequest = $showOrbitManagementRequest;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showOrbitManagementRequest->getColonyId(),
            $userId
        );

        $shipList = $this->shipRepository->getByInnerSystemLocation(
            $colony->getSystemsId(),
            $colony->getSX(),
            $colony->getSY()
        );

        $groupedList = [];

        foreach ($shipList as $ship) {
            $fleetId = $ship->getFleetId();

            $fleet = $groupedList[$fleetId] ?? null;
            if ($fleet === null) {
                $groupedList[$fleetId] = [];
            }

            $groupedList[$fleetId][] = $this->colonyLibFactory->createOrbitShipItem($ship, $userId);
        }

        $list = [];

        foreach ($groupedList as $fleetId => $shipList) {
            $list[] = $this->colonyLibFactory->createOrbitFleetItem(
                $fleetId,
                $shipList,
                $userId
            );
        }

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getName()
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d',
                static::VIEW_IDENTIFIER,
                $colony->getId()),
            _('Orbitalmanagement')
        );
        $game->setPagetitle(sprintf('%s Orbit', $colony->getName()));
        $game->setTemplateFile('html/orbitalmanagement.xhtml');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('ORBIT_SHIP_LIST', $list);
    }
}
