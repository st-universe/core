<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairShip;

use Colfields;
use request;
use RumpBuildingFunction;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    private $colonyLoader;

    private $colonyShipRepairRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $fieldId = (int)request::indInt('fid');

        $field = Colfields::getByColonyField($fieldId, $colony->getId());

        $game->setView("SHOW_SHIP_REPAIR");
        // XXX TBD

        $ship_id = request::getIntFatal('ship_id');

        $repairableShiplist = [];
        foreach ($colony->getOrbitShipList() as $fleet) {
            foreach ($fleet['ships'] as $ship_id => $ship) {
                if (!$ship->canBeRepaired() || $ship->getState() == SHIP_STATE_REPAIR) {
                    continue;
                }
                foreach (RumpBuildingFunction::getByRumpId($ship->getRumpId()) as $rump_rel) {
                    if ($field->getBuilding()->hasFunction($rump_rel->getBuildingFunction())) {
                        $repairableShiplist[$ship->getId()] = $ship;
                        break;
                    }
                }
            }
        }

        /**
         * @var \Ship $ship
         */
        $ship = ResourceCache()->getObject(CACHE_SHIP, $ship_id);
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
        $obj->setFieldId($field->getFieldId());
        $this->colonyShipRepairRepository->save($obj);

        $ship->setState(SHIP_STATE_REPAIR);
        $ship->save();

        $jobs = $this->colonyShipRepairRepository->getByColonyField(
            $colony->getId(),
            $field->getFieldId()
        );

        if (count($jobs) > 1) {
            $game->addInformation(_('Das Schiff wurde zur Reparaturwarteschlange hinzugefügt'));
            return;
        }
        $ticks = ceil(($ship->getMaxHuell() - $ship->getHuell()) / $ship->getRepairRate());
        $game->addInformation(sprintf(_('Das Schiff wird repariert. Fertigstellung in %d Runden'), $ticks));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
