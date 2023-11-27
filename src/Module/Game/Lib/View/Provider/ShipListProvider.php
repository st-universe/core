<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipListProvider implements ViewComponentProviderInterface
{
    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private SessionInterface $session;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        SessionInterface $session
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->session = $session;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $fleets = $this->fleetRepository->getByUser($userId);
        $ships = $this->shipRepository->getByUserAndFleetAndType($userId, null, SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP);

        foreach ($fleets as $fleet) {
            $fleet->setHiddenStyle($this->session->hasSessionValue('hiddenshiplistfleets', $fleet->getId()) ? 'display: none' : '');
        }

        $game->setTemplateVar('MAX_CREW_PER_FLEET', GameEnum::CREW_PER_FLEET);
        $game->setTemplateVar('SHIPS_AVAILABLE', $fleets !== [] || $ships !== []);
        $game->setTemplateVar('FLEETWRAPPERS', $this->shipWrapperFactory->wrapFleets($fleets));
        $game->setTemplateVar('SINGLESHIPWRAPPERS', $this->shipWrapperFactory->wrapShips($ships));
    }
}
