<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShiplistFleet;

use Override;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class ShowShiplistFleet implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIPLIST_FLEET';

    public function __construct(
        private readonly FleetRepositoryInterface $fleetRepository,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly SessionStorageInterface $sessionStorage
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $fleetId = request::getIntFatal('fleetid');

        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null) {
            return;
        }

        if ($fleet->getUser()->getId() !== $game->getUser()->getId()) {
            throw new AccessViolationException(sprintf('tried to refresh foreign fleet, idiot: %d', $userId));
        }
        $fleet->setHiddenStyle($this->sessionStorage->hasSessionValue('hiddenshiplistfleets', $fleetId) ? 'display: none' : '');

        $game->showMacro('html/shiplistFleetform.twig');

        $game->setTemplateVar('FLEETWRAPPER', $this->spacecraftWrapperFactory->wrapFleet($fleet));
        $game->setTemplateVar('MAX_CREW_PER_FLEET', GameEnum::CREW_PER_FLEET);
    }
}
