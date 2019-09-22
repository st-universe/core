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
use VisualNavPanel;

final class ShowShip implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP';

    private $session;

    private $shipLoader;

    private $researchedRepository;

    private $fleetRepository;

    public function __construct(
        SessionInterface $session,
        ShipLoaderInterface $shipLoader,
        ResearchedRepositoryInterface $researchedRepository,
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->session = $session;
        $this->shipLoader = $shipLoader;
        $this->researchedRepository = $researchedRepository;
        $this->fleetRepository = $fleetRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $shipId = $ship->getId();

        if ($ship->getSystemsId() > 0) {
            $coords_query = sprintf(
                'systems_id = %d AND sx = %d AND sy = %d',
                $ship->getSystemsId(),
                $ship->getPosX(),
                $ship->getPosY()
            );
        } else {
            $coords_query = sprintf('systems_id=0 AND cx = %d AND cy = %d', $ship->getPosX(), $ship->getPosY());
        }

        $result = DB()->query(
            sprintf(
                'SELECT id FROM stu_ships WHERE id != %d AND is_base=0 AND fleets_id>0 AND cloak=0 AND %s',
                $shipId,
                $coords_query
            )
        );
        $fnbs = [];
        while ($data = mysqli_fetch_assoc($result)) {
            $obj = $this->shipLoader->getById((int) $data['id']);
            if (!array_key_exists($obj->getFleetId(), $fnbs)) {
                $fnbs[$obj->getFleetId()]['fleet'] = $this->fleetRepository->find((int) $obj->getFleetId());
                if ($this->session->hasSessionValue('hiddenfleets', $obj->getFleetId())) {
                    $fnbs[$obj->getFleetId()]['fleethide'] = true;
                } else {
                    $fnbs[$obj->getFleetId()]['fleethide'] = false;
                }
            }
            $fnbs[$obj->getFleetId()]['ships'][] = $obj;
        }

        $result = DB()->query(
            sprintf(
                'SELECT id FROM stu_ships WHERE id != %d AND is_base=1 AND cloak=0 AND %s',
                $shipId,
                $coords_query
            )
        );
        $nbs = [];
        while ($data = mysqli_fetch_assoc($result)) {
            $nbs[] = ResourceCache()->getObject('ship', $data['id']);
        }

        $result = DB()->query(
            sprintf(
                'SELECT id FROM stu_ships WHERE id != %d AND is_base=0 AND fleets_id=0 AND cloak=0 AND %s',
                $shipId,
                $coords_query
            )
        );
        $singleShipsNbs = array();
        while ($data = mysqli_fetch_assoc($result)) {
            $singleShipsNbs[] = ResourceCache()->getObject('ship', $data['id']);
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
