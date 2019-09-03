<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Colonize;

use Building;
use Colfields;
use Colony;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRumpColonizationBuildingRepositoryInterface;

final class Colonize implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONIZE';

    private $shipLoader;

    private $shipRumpColonizationBuildingRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRumpColonizationBuildingRepository = $shipRumpColonizationBuildingRepository;
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

        $researchId = $colony->getPlanetType()->getResearchId();

        if (
            ($researchId > 0 && !$game->getUser()->hasResearched($researchId)) ||
            !$colony->isFree()
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

        $base_building = $this->shipRumpColonizationBuildingRepository->findByShipRump((int) $ship->getRumpId());
        if ($base_building === null) {
            return;
        }

        $colony->colonize($userId, new Building($base_building->getBuildingId()), $field);
        $ship->deactivateTraktorBeam();
        $ship->changeFleetLeader();
        $ship->remove();

        $game->redirectTo(sprintf(
            '/colony.php?%s=1&id=%d',
            ShowColony::VIEW_IDENTIFIER,
            $colony->getId()
        ));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
