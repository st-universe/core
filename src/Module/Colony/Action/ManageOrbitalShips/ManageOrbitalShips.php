<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ManageOrbitalShips;

use request;
use Stu\Lib\ShipManagement\HandleManagersInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderFactoryInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagement;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ManageOrbitalShips implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MANAGE_SHIPS';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRepositoryInterface $shipRepository;

    private InteractionCheckerInterface $interactionChecker;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ManagerProviderFactoryInterface $managerProviderFactory;

    private HandleManagersInterface $handleManagers;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyRepositoryInterface $colonyRepository,
        ShipRepositoryInterface $shipRepository,
        InteractionCheckerInterface $interactionChecker,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ManagerProviderFactoryInterface $managerProviderFactory,
        HandleManagersInterface $handleManagers,
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyRepository = $colonyRepository;
        $this->shipRepository = $shipRepository;
        $this->interactionChecker = $interactionChecker;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->managerProviderFactory = $managerProviderFactory;
        $this->handleManagers = $handleManagers;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowOrbitManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $shipIds = request::postArray('ships');
        if (count($shipIds) == 0) {
            $game->addInformation(_('Es wurden keine Schiffe ausgewählt'));
            return;
        }
        $msg = [];

        $managerProvider = $this->managerProviderFactory->getManagerProviderColony($colony);

        $values = [
            'batt' => request::postArray('batt'),
            'man' => request::postArray('man'),
            'unman' => request::postArray('unman'),
            'reactor' => request::postArray('reactor'),
            'torp' => request::postArray('torp'),
            'torp_type' => request::postArray('torp_type'),
        ];

        foreach ($shipIds as $shipId) {
            $msg = array_merge($msg, $this->handleShip($values, $managerProvider, (int)$shipId, $colony));
        }
        $this->colonyRepository->save($colony);

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
        ColonyInterface $colony
    ): array {
        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            return [];
        }
        if ($ship->getCloakState()) {
            return [];
        }
        if (!$this->interactionChecker->checkColonyPosition($colony, $ship)) {
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
