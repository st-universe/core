<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use Stu\Component\Game\GameEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\BuoyRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipListProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private ShipRepositoryInterface $shipRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private BuoyRepositoryInterface $buoyRepository,
        private SessionInterface $session
    ) {}

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $fleets = $this->fleetRepository->getByUser($userId);
        $singleShips = $this->shipRepository->getByUserAndFleet($userId, null);
        $buoys = $this->buoyRepository->findByUserId($userId);

        foreach ($fleets as $fleet) {
            $fleet->setHiddenStyle($this->session->hasSessionValue('hiddenshiplistfleets', $fleet->getId()) ? 'display: none' : '');
        }

        $game->setTemplateVar('MAX_CREW_PER_FLEET', GameEnum::CREW_PER_FLEET);
        $game->setTemplateVar('SHIPS_AVAILABLE', $fleets !== [] || $singleShips !== []);
        $game->setTemplateVar('FLEET_WRAPPERS', $this->spacecraftWrapperFactory->wrapFleets($fleets));
        $game->setTemplateVar('SINGLESHIPWRAPPERS', $this->spacecraftWrapperFactory->wrapShips($singleShips));
        $game->setTemplateVar('BUOYS', $buoys);
    }
}
