<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Colonize;

use request;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRumpColonizationBuildingRepositoryInterface;

final class Colonize implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONIZE';

    private ShipLoaderInterface $shipLoader;

    private ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private PlanetColonizationInterface $planetColonization;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRemoverInterface $shipRemover;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository,
        ResearchedRepositoryInterface $researchedRepository,
        BuildingRepositoryInterface $buildingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PlanetColonizationInterface $planetColonization,
        ColonyRepositoryInterface $colonyRepository,
        ShipRemoverInterface $shipRemover,
        PositionCheckerInterface $positionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRumpColonizationBuildingRepository = $shipRumpColonizationBuildingRepository;
        $this->researchedRepository = $researchedRepository;
        $this->buildingRepository = $buildingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->planetColonization = $planetColonization;
        $this->colonyRepository = $colonyRepository;
        $this->shipRemover = $shipRemover;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

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
        if (!$this->positionChecker->checkColonyPosition($colony, $ship)) {
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

        $this->shipRemover->remove($ship);

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
