<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAvailableShips;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowAvailableShips implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_AVAILABLE_SHIPS';

    public function __construct(private FleetRepositoryInterface $fleetRepository, private ShipRepositoryInterface $shipRepository) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $user = $game->getUser();

        $fleet = $this->fleetRepository->find(request::getIntFatal('fleetid'));

        if ($fleet === null || $fleet->getUserId() !== $user->getId()) {
            throw new AccessViolationException();
        }

        $game->setPageTitle(_('Schiff(e) hinzufügen'));
        $game->setMacroInAjaxWindow('html/ship/showavailableships.twig');

        $game->setTemplateVar('FLEET', $fleet);
        $game->setTemplateVar('AVAILABLE_SHIPS', $this->shipRepository->getPossibleFleetMembers($fleet->getLeadShip()));
    }
}
