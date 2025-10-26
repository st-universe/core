<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ManageShips;

use request;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\SpacecraftManagement\HandleManagersInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderFactoryInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagement;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ManageShips implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MANAGE_SPACECRAFTS';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private InteractionCheckerInterface $interactionChecker,
        private StationUtilityInterface $stationUtility,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private ManagerProviderFactoryInterface $managerProviderFactory,
        private HandleManagersInterface $handleManagers
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $station = $wrapper->get();

        if (!$this->stationUtility->canManageShips($station)) {
            return;
        }

        $shipIds = request::postArray('spacecrafts');
        if (count($shipIds) == 0) {
            $game->getInfo()->addInformation(_('Es wurden keine Schiffe ausgewählt'));
            return;
        }

        $msg = [];

        $managerProvider = $this->managerProviderFactory->getManagerProviderStation($wrapper);

        $values = [
            'batt' => request::postArray('batt'),
            'crew' => request::postArray('crew'),
            'reactor' => request::postArray('reactor'),
            'torp' => request::postArray('torp'),
            'torp_type' => request::postArray('torp_type'),
        ];

        foreach ($shipIds as $shipId) {
            $msg = array_merge($msg, $this->handleShip($values, $managerProvider, (int)$shipId, $station));
        }

        $this->spacecraftRepository->save($station);

        $game->getInfo()->addInformationArray($msg, true);
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
        Station $station
    ): array {
        $ship = $this->spacecraftRepository->find($shipId);
        if ($ship === null) {
            return [];
        }
        if ($ship->isCloaked()) {
            return [];
        }
        if (!$this->interactionChecker->checkPosition($station, $ship)) {
            return [];
        }

        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($ship);

        $msg = $this->handleManagers->handle($wrapper, $values, $managerProvider);

        $this->spacecraftRepository->save($ship);

        return $msg;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
