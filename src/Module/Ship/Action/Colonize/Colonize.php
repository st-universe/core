<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Colonize;

use request;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRumpColonizationBuildingRepositoryInterface;

final class Colonize implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONIZE';

    private ShipLoaderInterface $shipLoader;

    private ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private PlanetColonizationInterface $planetColonization;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRemoverInterface $shipRemover;

    private PositionCheckerInterface $positionChecker;

    private ColonizationCheckerInterface $colonizationChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository,
        BuildingRepositoryInterface $buildingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PlanetColonizationInterface $planetColonization,
        ColonyRepositoryInterface $colonyRepository,
        ShipRemoverInterface $shipRemover,
        PositionCheckerInterface $positionChecker,
        ColonizationCheckerInterface $colonizationChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRumpColonizationBuildingRepository = $shipRumpColonizationBuildingRepository;
        $this->buildingRepository = $buildingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->planetColonization = $planetColonization;
        $this->colonyRepository = $colonyRepository;
        $this->shipRemover = $shipRemover;
        $this->positionChecker = $positionChecker;
        $this->colonizationChecker = $colonizationChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colonyId = (int)request::getIntFatal('colid');
        $fieldId = (int)request::getIntFatal('field');

        $colony = $this->colonyRepository->find($colonyId);
        $field = $this->planetFieldRepository->find($fieldId);

        if ($field === null || $colony === null) {
            return;
        }

        if (!$ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
            return;
        }

        if ($colony->getId() != $field->getColonyId()) {
            return;
        }
        if (!$this->positionChecker->checkColonyPosition($colony, $ship)) {
            return;
        }
        if ($this->colonizationChecker->canColonize($user, $colony) === false) {
            return;
        }

        $base_building = $this->shipRumpColonizationBuildingRepository->findByShipRump($ship->getRump());
        if ($base_building === null) {
            return;
        }

        $this->planetColonization->colonize(
            $colony,
            $userId,
            $this->buildingRepository->find($base_building->getBuildingId()),
            $field
        );

        $this->transferCrewToColony($ship, $colony);

        $this->shipRemover->remove($ship);

        $game->checkDatabaseItem($colony->getPlanetType()->getDatabaseId());

        $game->redirectTo(sprintf(
            '/colony.php?%s=1&id=%d',
            ShowColony::VIEW_IDENTIFIER,
            $colony->getId()
        ));
    }

    private function transferCrewToColony(ShipInterface $ship, ColonyInterface $colony): void
    {
        foreach ($ship->getCrewlist() as $crewAssignment) {
            $crewAssignment->setColony($colony);
            $crewAssignment->setShip(null);
            $crewAssignment->setSlot(null);
            $colony->getCrewAssignments()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);
        }

        $ship->getCrewlist()->clear();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
