<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Colonize;

use Colony;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRumpColonizationBuildingRepositoryInterface;

final class Colonize implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONIZE';

    private $shipLoader;

    private $shipRumpColonizationBuildingRepository;

    private $researchedRepository;

    private $buildingRepository;

    private $planetFieldRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository,
        ResearchedRepositoryInterface $researchedRepository,
        BuildingRepositoryInterface $buildingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRumpColonizationBuildingRepository = $shipRumpColonizationBuildingRepository;
        $this->researchedRepository = $researchedRepository;
        $this->buildingRepository = $buildingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
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
        $fieldId = (int) request::getIntFatal('field');
        $colony = new Colony($colonyId);

        $field = $this->planetFieldRepository->find($fieldId);

        if ($field === null) {
            return;
        }

        if (!$ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
            return;
        }

        $researchId = (int) $colony->getPlanetType()->getResearchId();

        if (
            ($researchId > 0 && $this->researchedRepository->hasUserFinishedResearch($researchId, $userId) == false) ||
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

        $colony->colonize($userId, $this->buildingRepository->find($base_building->getBuildingId()), $field);
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
