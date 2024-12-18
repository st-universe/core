<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairShip;

use Override;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository, private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository, private PlanetFieldRepositoryInterface $planetFieldRepository, private ShipRepositoryInterface $shipRepository, private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory, private OrbitShipListRetrieverInterface $orbitShipListRetriever, private InteractionCheckerInterface $interactionChecker, private PrivateMessageSenderInterface $privateMessageSender, private ColonyFunctionManagerInterface $colonyFunctionManager) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_SHIP_REPAIR);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $ship = $this->shipRepository->find(request::indInt('ship_id'));
        if ($ship === null) {
            $game->addInformation(_('Das Schiff existiert nicht'));
            return;
        }

        if (!$this->interactionChecker->checkPosition($colony, $ship)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', self::ACTION_IDENTIFIER);
        }

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            request::indInt('fid'),
        );

        if ($field === null || $field->getBuilding() === null) {
            $game->addInformation(_('Es ist keine Werft vorhanden'));
            return;
        }


        $fieldFunctions = $field->getBuilding()->getFunctions()->toArray();

        /**@var array<int, ShipWrapperInterface> */
        $repairableShiplist = [];
        foreach ($this->orbitShipListRetriever->retrieve($colony) as $fleet) {
            $ships = array_filter($fleet['ships'], fn($ship) => $ship instanceof ShipInterface);

            foreach ($ships as $orbitShip) {
                $wrapper = $this->spacecraftWrapperFactory->wrapShip($orbitShip);
                if (!$wrapper->canBeRepaired() || $orbitShip->isUnderRepair()) {
                    continue;
                }
                foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($orbitShip->getRump()) as $rump_rel) {
                    if (array_key_exists($rump_rel->getBuildingFunction()->value, $fieldFunctions)) {
                        $repairableShiplist[$orbitShip->getId()] = $wrapper;
                        break;
                    }
                }
            }
        }


        if (!array_key_exists($ship->getId(), $repairableShiplist)) {
            $game->addInformation(_('Das Schiff kann nicht repariert werden.'));
            return;
        }

        if ($colony->isBlocked()) {
            $game->addInformation(_('Schiffsreparatur ist nicht möglich während die Kolonie blockiert wird'));
            return;
        }

        $wrapper = $this->spacecraftWrapperFactory->wrapShip($ship);
        if (!$wrapper->canBeRepaired()) {
            $game->addInformation(_('Das Schiff kann nicht repariert werden.'));
            return;
        }

        if ($ship->getState() == SpacecraftStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $game->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $obj = $this->colonyShipRepairRepository->prototype();
        $obj->setColony($colony);
        $obj->setShip($ship);
        $obj->setFieldId($field->getFieldId());
        $this->colonyShipRepairRepository->save($obj);

        $ship->setState(SpacecraftStateEnum::SHIP_STATE_REPAIR_PASSIVE);

        $this->shipRepository->save($ship);

        $jobs = $this->colonyShipRepairRepository->getByColonyField(
            $colony->getId(),
            $field->getFieldId()
        );

        if (count($jobs) > 1) {
            $game->addInformation(_('Das Schiff wurde zur Reparaturwarteschlange hinzugefügt'));
            return;
        }

        $wrapper = $repairableShiplist[$ship->getId()];

        $ticks = $wrapper->getRepairDuration();
        $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD);
        if ($isRepairStationBonus) {
            $ticks = ceil($ticks * 0.5);
        }

        $game->addInformationf(_('Das Schiff wird repariert. Fertigstellung in %d Runden'), $ticks);

        $this->privateMessageSender->send(
            $userId,
            $ship->getUser()->getId(),
            sprintf(
                "Die %s wird in Sektor %s bei der Kolonie %s des Spielers %s repariert. Fertigstellung in %d Runden.",
                $ship->getName(),
                $ship->getSectorString(),
                $colony->getName(),
                $colony->getUser()->getName(),
                $ticks
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
