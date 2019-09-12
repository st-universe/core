<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairShip;

use Colfields;
use request;
use Ship;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepair;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    private $colonyLoader;

    private $colonyShipRepairRepository;

    private $shipRumpBuildingFunctionRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipRepair::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $field = Colfields::getByColonyField(
            (int)request::indInt('fid'),
            $colony->getId()
        );

        $repairableShiplist = [];
        foreach ($colony->getOrbitShipList($userId) as $fleet) {
            /**
             * @var Ship $ship
             */
            foreach ($fleet['ships'] as $ship_id => $ship) {
                if (!$ship->canBeRepaired() || $ship->getState() == SHIP_STATE_REPAIR) {
                    continue;
                }
                foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump((int) $ship->getRumpId()) as $rump_rel) {
                    if (array_key_exists($rump_rel->getBuildingFunction(), $field->getBuilding()->getFunctions())) {
                        $repairableShiplist[$ship->getId()] = $ship;
                        break;
                    }
                }
            }
        }

        /**
         * @var Ship $ship
         */
        $ship = ResourceCache()->getObject(CACHE_SHIP, (int) request::getIntFatal('ship_id'));
        if (!array_key_exists($ship->getId(), $repairableShiplist)) {
            return;
        }
        if (!$ship->canBeRepaired()) {
            $game->addInformation(_('Das Schiff kann nicht repariert werden'));
            return;
        }
        if ($ship->getState() == SHIP_STATE_REPAIR) {
            $game->addInformation(_('Das Schiff wird bereits repariert'));
            return;
        }

        $obj = $this->colonyShipRepairRepository->prototype();
        $obj->setColonyId($colony->getId());
        $obj->setShipId($ship_id);
        $obj->setFieldId((int) $field->getFieldId());
        $this->colonyShipRepairRepository->save($obj);

        $ship->setState(SHIP_STATE_REPAIR);
        $ship->save();

        $jobs = $this->colonyShipRepairRepository->getByColonyField(
            (int) $colony->getId(),
            (int) $field->getFieldId()
        );

        if (count($jobs) > 1) {
            $game->addInformation(_('Das Schiff wurde zur Reparaturwarteschlange hinzugefügt'));
            return;
        }
        $ticks = ceil(($ship->getMaxHuell() - $ship->getHuell()) / $ship->getRepairRate());
        $game->addInformationf(_('Das Schiff wird repariert. Fertigstellung in %d Runden'), $ticks);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
