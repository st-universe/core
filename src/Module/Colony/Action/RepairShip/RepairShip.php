<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairShip;

use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Colony\OrbitShipWrappersRetrieverInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private ShipRepositoryInterface $shipRepository,
        private OrbitShipWrappersRetrieverInterface $orbitShipWrappersRetriever,
        private InteractionCheckerInterface $interactionChecker,
        private PrivateMessageSenderInterface $privateMessageSender,
        private ColonyFunctionManagerInterface $colonyFunctionManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_SHIP_REPAIR);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $target = $this->shipRepository->find(request::indInt('ship_id'));
        if ($target === null) {
            $game->getInfo()->addInformation('Das Schiff existiert nicht');
            return;
        }

        if (!$this->interactionChecker->checkPosition($colony, $target)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', self::ACTION_IDENTIFIER);
        }

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            request::indInt('fid'),
        );

        if ($field === null || $field->getBuilding() === null) {
            $game->getInfo()->addInformation('Es ist keine Werft vorhanden');
            return;
        }


        $fieldFunctions = $field->getBuilding()->getFunctions()->toArray();

        /**@var array<int, ShipWrapperInterface> */
        $repairableShipWrappers = [];
        foreach ($this->orbitShipWrappersRetriever->retrieve($colony) as $group) {

            foreach ($group->getWrappers() as $wrapper) {
                $ship = $wrapper->get();
                if (!$wrapper->canBeRepaired() || $ship->getCondition()->isUnderRepair()) {
                    continue;
                }
                foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                    if (array_key_exists($rump_rel->getBuildingFunction()->value, $fieldFunctions)) {
                        $repairableShipWrappers[$ship->getId()] = $wrapper;
                        break;
                    }
                }
            }
        }

        if (!array_key_exists($target->getId(), $repairableShipWrappers)) {
            $game->getInfo()->addInformation('Das Schiff kann nicht repariert werden.');
            return;
        }

        if ($colony->isBlocked()) {
            $game->getInfo()->addInformation('Schiffsreparatur ist nicht möglich während die Kolonie blockiert wird');
            return;
        }

        if ($target->getState() == SpacecraftStateEnum::ASTRO_FINALIZING) {
            $game->getInfo()->addInformation('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.');
            return;
        }

        $obj = $this->colonyShipRepairRepository->prototype();
        $obj->setColony($colony);
        $obj->setShip($target);
        $obj->setFieldId($field->getFieldId());
        $this->colonyShipRepairRepository->save($obj);

        $target->getCondition()->setState(SpacecraftStateEnum::REPAIR_PASSIVE);

        $jobs = $this->colonyShipRepairRepository->getByColonyField(
            $colony->getId(),
            $field->getFieldId()
        );

        if (count($jobs) > 1) {
            $game->getInfo()->addInformation('Das Schiff wurde zur Reparaturwarteschlange hinzugefügt');
            return;
        }

        $wrapper = $repairableShipWrappers[$target->getId()];
        $ticks = $wrapper->getRepairDuration();
        $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::REPAIR_SHIPYARD);
        if ($isRepairStationBonus) {
            $ticks = ceil($ticks * 0.5);
        }

        $activemsg = $field->isActive() ? '' : ', nach aktivierung der Werft';
        $game->getInfo()->addInformationf('Das Schiff wird repariert. Fertigstellung in %d Runden%s', $ticks, $activemsg);

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            sprintf(
                "Die %s wird in Sektor %s bei der Kolonie %s des Spielers %s repariert. Fertigstellung in %d Runden%s",
                $target->getName(),
                $target->getSectorString(),
                $colony->getName(),
                $colony->getUser()->getName(),
                $ticks,
                $activemsg
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
