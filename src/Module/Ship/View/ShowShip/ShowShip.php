<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use Fleet;
use NavPanel;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use VisualNavPanel;

final class ShowShip implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP';

    private $session;

    private $shipLoader;

    public function __construct(
        SessionInterface $session,
        ShipLoaderInterface $shipLoader
    ) {
        $this->session = $session;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $shipId = $ship->getId();

        if ($ship->isInSystem()) {
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
                $fnbs[$obj->getFleetId()]['fleet'] = new Fleet($obj->getFleetId());
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
        if ($colony && $ship->getRump()->canColonize()) {
            $researchId = $colony->getPlanetType()->getResearchId();
            $canColonize = ($researchId == 0 || ($researchId > 0 && $game->getUser()->hasResearched($researchId))) && $colony->isFree();
        }

        $game->appendNavigationPart(
            'ship.php',
            _('Schiffe')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', static::VIEW_IDENTIFIER, $shipId),
            $ship->getNameWithoutMarkup()
        );
        $game->setPagetitle($ship->getNameWithoutMarkup());
        $game->setTemplateFile('html/ship.xhtml');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('VISUAL_NAV_PANEL', new VisualNavPanel($ship));
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
