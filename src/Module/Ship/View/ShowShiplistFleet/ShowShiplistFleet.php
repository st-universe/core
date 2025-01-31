<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShiplistFleet;

use Override;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Exception\AccessViolation;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class ShowShiplistFleet implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIPLIST_FLEET';

    public function __construct(private FleetRepositoryInterface $fleetRepository, private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory, private SessionInterface $session) {}

    #[Override]
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

        $game->setTemplateVar('FLEETWRAPPER', $this->spacecraftWrapperFactory->wrapFleet($fleet));
        $game->setTemplateVar('MAX_CREW_PER_FLEET', GameEnum::CREW_PER_FLEET);
    }
}
