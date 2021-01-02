<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairShip;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepair;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipRepair::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            (int)request::indInt('fid'),
        );

        if ($field === null) {
            return;
        }

        $fieldFunctions = $field->getBuilding()->getFunctions()->toArray();

        $repairableShiplist = [];
        foreach ($colony->getOrbitShipList($userId) as $fleet) {
            /**
             * @var ShipInterface $ship
             */
            foreach ($fleet['ships'] as $ship_id => $ship) {
                if (!$ship->canBeRepaired() || $ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR) {
                    continue;
                }
                foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                    if (array_key_exists($rump_rel->getBuildingFunction(), $fieldFunctions)) {
                        $repairableShiplist[$ship->getId()] = $ship;
                        break;
                    }
                }
            }
        }

        $ship = $this->shipRepository->find((int) request::getIntFatal('ship_id'));
        if ($ship === null || !array_key_exists($ship->getId(), $repairableShiplist)) {
            return;
        }
        if (!$ship->canBeRepaired()) {
            $game->addInformation(_('Das Schiff kann nicht repariert werden'));
            return;
        }
        if ($ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR) {
            $game->addInformation(_('Das Schiff wird bereits repariert'));
            return;
        }

        $obj = $this->colonyShipRepairRepository->prototype();
        $obj->setColony($colony);
        $obj->setShip($ship);
        $obj->setFieldId((int) $field->getFieldId());
        $this->colonyShipRepairRepository->save($obj);

        $ship->setState(ShipStateEnum::SHIP_STATE_REPAIR);

        $this->shipRepository->save($ship);

        $jobs = $this->colonyShipRepairRepository->getByColonyField(
            (int) $colony->getId(),
            (int) $field->getFieldId()
        );

        if (count($jobs) > 1) {
            $game->addInformation(_('Das Schiff wurde zur Reparaturwarteschlange hinzugefügt'));
            return;
        }
        $ticks = ceil(($ship->getMaxHuell() - $ship->getHuell()) / $ship->getRepairRate());
        $ticks = max($ticks, ceil(count($ship->getDamagedSystems()) / 2));
        
        $game->addInformationf(_('Das Schiff wird repariert. Fertigstellung in %d Runden'), $ticks);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
