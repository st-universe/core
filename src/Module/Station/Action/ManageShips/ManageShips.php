<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ManageShips;

use request;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\ShipManagement\HandleManagersInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderFactoryInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagement;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ManageShips implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MANAGE_STATION_SHIPS';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private InteractionCheckerInterface $interactionChecker;

    private StationUtilityInterface $stationUtility;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ManagerProviderFactoryInterface $managerProviderFactory;

    private HandleManagersInterface $handleManagers;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        InteractionCheckerInterface $interactionChecker,
        StationUtilityInterface $stationUtility,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ManagerProviderFactoryInterface $managerProviderFactory,
        HandleManagersInterface $handleManagers,
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->interactionChecker = $interactionChecker;
        $this->stationUtility = $stationUtility;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->managerProviderFactory = $managerProviderFactory;
        $this->handleManagers = $handleManagers;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $station = $wrapper->get();

        if (!$this->stationUtility->canManageShips($station)) {
            return;
        }

        $shipIds = request::postArray('ships');
        if (count($shipIds) == 0) {
            $game->addInformation(_('Es wurden keine Schiffe ausgewählt'));
            return;
        }

        $msg = [];

        $managerProvider = $this->managerProviderFactory->getManagerProviderStation($wrapper);

        $values = [
            'batt' => request::postArray('batt'),
            'man' => request::postArray('man'),
            'unman' => request::postArray('unman'),
            'reactor' => request::postArray('reactor'),
            'torp' => request::postArray('torp'),
            'torp_type' => request::postArray('torp_type'),
        ];

        foreach ($shipIds as $shipId) {
            $msg = array_merge($msg, $this->handleShip($values, $managerProvider, (int)$shipId, $station));
        }

        $this->shipRepository->save($station);

        $game->addInformationMerge($msg);
    }

    /**
     * @param array<string, array<int|string, mixed>> $values
     *
     * @return array<string>
     */
    private function handleShip(
        array $values,
        ManagerProviderInterface $managerProvider,
        int $shipId,
        ShipInterface $station
    ): array {
        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            return [];
        }
        if ($ship->getCloakState()) {
            return [];
        }
        if (!$this->interactionChecker->checkPosition($station, $ship)) {
            return [];
        }
        if ($ship->isDestroyed()) {
            return [];
        }

        $wrapper = $this->shipWrapperFactory->wrapShip($ship);

        $msg = $this->handleManagers->handle($wrapper, $values, $managerProvider);

        $this->shipRepository->save($ship);

        return $msg;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
