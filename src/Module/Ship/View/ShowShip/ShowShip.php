<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use NavPanel;
use request;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Ship\Lib\FleetNfsItem;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use VisualNavPanel;

final class ShowShip implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP';

    private SessionInterface $session;

    private ShipLoaderInterface $shipLoader;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonizationCheckerInterface $colonizationChecker;

    public function __construct(
        SessionInterface $session,
        ShipLoaderInterface $shipLoader,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        ColonizationCheckerInterface $colonizationChecker
    ) {
        $this->session = $session;
        $this->shipLoader = $shipLoader;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->colonizationChecker = $colonizationChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $ownsCurrentColony = false;

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colony = $this->colonyRepository->getByPosition(
            $ship->getSystem(),
            $ship->getPosX(),
            $ship->getPosY()
        );

        $shipId = $ship->getId();

        $nbs = $this->shipRepository->getBaseScannerResults(
            $ship->getSystem(),
            $ship->getSx(),
            $ship->getSy(),
            $ship->getCx(),
            $ship->getCy(),
            $ship->getId()
        );

        $singleShipsNbs = $this->shipRepository->getSingleShipScannerResults(
            $ship->getSystem(),
            $ship->getSx(),
            $ship->getSy(),
            $ship->getCx(),
            $ship->getCy(),
            $ship->getId()
        );

        $fleets = $this->fleetRepository->getByPositition(
            $ship->getSystem(),
            $ship->getCx(),
            $ship->getCy(),
            $ship->getSx(),
            $ship->getSy()
        );

        $fnbs = [];
        foreach ($fleets as $fleet) {
            $fnbs[] = new FleetNfsItem(
                $this->session,
                $fleet,
                $ship
            );
        }

        $canColonize = false;
        if ($colony) {
            if ($ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
                $canColonize = $this->colonizationChecker->canColonize($user, $colony);
            }
            $ownsCurrentColony = $colony->getUser() === $user;
        }

        $game->appendNavigationPart(
            'ship.php',
            _('Schiffe')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', static::VIEW_IDENTIFIER, $shipId),
            $ship->getName()
        );
        $game->setPagetitle($ship->getName());
        $game->setTemplateFile('html/ship.xhtml');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('VISUAL_NAV_PANEL', new VisualNavPanel($ship, $game->getUser()));
        $game->setTemplateVar('NAV_PANEL', new NavPanel($ship));
        $game->setTemplateVar(
            'HAS_NBS',
            $fnbs !== [] || $nbs !== [] || $singleShipsNbs !== []
        );
        $game->setTemplateVar('FLEET_NBS', $fnbs);
        $game->setTemplateVar('STATION_NBS', $nbs);
        $game->setTemplateVar('SHIP_NBS', $singleShipsNbs);
        $game->setTemplateVar('CAN_COLONIZE_CURRENT_COLONY', $canColonize);
        $game->setTemplateVar('OWNS_CURRENT_COLONY', $ownsCurrentColony);
        $game->setTemplateVar('CURRENT_COLONY', $colony);
    }
}
