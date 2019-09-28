<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use NavPanel;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use VisualNavPanel;

final class ShowShip implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP';

    private $session;

    private $shipLoader;

    private $researchedRepository;

    private $fleetRepository;

    private $shipRepository;

    public function __construct(
        SessionInterface $session,
        ShipLoaderInterface $shipLoader,
        ResearchedRepositoryInterface $researchedRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->session = $session;
        $this->shipLoader = $shipLoader;
        $this->researchedRepository = $researchedRepository;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $shipId = $ship->getId();

        $result = $this->shipRepository->getFleetScannerResults(
            $ship->getSystem(),
            $ship->getSx(),
            $ship->getSy(),
            $ship->getCx(),
            $ship->getCy(),
            $ship->getId()
        );

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

        $fnbs = [];
        foreach ($result as $data) {
            if (!array_key_exists($data->getFleetId(), $fnbs)) {
                $fnbs[$data->getFleetId()]['fleet'] = $data->getFleet();
                if ($this->session->hasSessionValue('hiddenfleets', $data->getFleetId())) {
                    $fnbs[$data->getFleetId()]['fleethide'] = true;
                } else {
                    $fnbs[$data->getFleetId()]['fleethide'] = false;
                }
            }
            $fnbs[$data->getFleetId()]['ships'][] = $data;
        }

        $colony = $ship->getCurrentColony();
        $canColonize = false;
        if ($colony && $ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
            $researchId = (int) $colony->getPlanetType()->getResearchId();
            $canColonize = $colony->isFree() && (
                $researchId === 0 || ($this->researchedRepository->hasUserFinishedResearch($researchId, $userId))
            );
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
    }
}
