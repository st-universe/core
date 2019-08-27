<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Colonize;

use Building;
use Colfields;
use Colony;
use request;
use RumpColonizeBuilding;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class Colonize implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONIZE';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colonyId = request::getIntFatal('colid');
        $fieldId = request::getIntFatal('field');
        $colony = new Colony($colonyId);
        $field = new Colfields($fieldId);

        if (!$ship->getRump()->canColonize()) {
            return;
        }

        if (
            !$colony->getPlanetType()->getResearchId() > 0 ||
            !$game->getUser()->hasResearched($colony->getPlanetType()->getResearchId())
            |$colony->isFree()
        ) {
            return;
        }
        if ($colony->getId() != $field->getColonyId()) {
            return;
        }
        if (!checkColonyPosition($colony, $ship)) {
            return;
        }
        if ($colony->getPlanetType()->getIsMoon()) {
            if ($game->getMoonColonyCount() >= $game->getMoonColonyLimit()) {
                $game->addInformation(_('Es können keine weiteren Monde besiedeln werden'));
                return;
            }
        } else {
            if ($game->getPlanetColonyCount() >= $game->getPlanetColonyLimit()) {
                $game->addInformation(_('Es können keine weiteren Planeten besiedeln werden'));
                return;
            }
        }
        $game->checkDatabaseItem($colony->getPlanetType()->getDatabaseId());
        $base_building = RumpColonizeBuilding::getByRump($ship->getRump()->getId());
        $colony->colonize(new Building($base_building->getBuildingId()), $field);
        $ship->deactivateTraktorBeam();
        $ship->changeFleetLeader();
        $ship->remove();

        DB()->commitTransaction();

        header('Location: colony.php?id=' . $colony->getId());
        exit;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
