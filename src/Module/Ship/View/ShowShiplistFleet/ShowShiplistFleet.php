<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShiplistFleet;

use request;
use Stu\Component\Game\GameEnum;
use Stu\Exception\AccessViolation;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class ShowShiplistFleet implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPLIST_FLEET';

    private FleetRepositoryInterface $fleetRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private SessionInterface $session;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        SessionInterface $session
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->session = $session;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $fleetId = request::getIntFatal('fleetid');

        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null) {
            return;
        }

        if ($fleet->getUser() !== $game->getUser()) {
            throw new AccessViolation(sprintf('tried to refresh foreign fleet, idiot: %d', $userId));
        }
        $fleet->setHiddenStyle($this->session->hasSessionValue('hiddenshiplistfleets', $fleetId) ? 'display: none' : '');

        $game->showMacro('html/shiplistFleetform.twig');

        $game->setTemplateVar('FLEETWRAPPER', $this->shipWrapperFactory->wrapFleet($fleet));
        $game->setTemplateVar('MAX_CREW_PER_FLEET', GameEnum::CREW_PER_FLEET);
    }
}
