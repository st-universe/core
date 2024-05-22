<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairShip;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Module\Control\ViewContextTypeEnum;

final class RepairShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    private InteractionCheckerInterface $interactionChecker;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        InteractionCheckerInterface $interactionChecker,
        PrivateMessageSenderInterface $privateMessageSender,
        ColonyFunctionManagerInterface $colonyFunctionManager
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->privateMessageSender = $privateMessageSender;
        $this->interactionChecker = $interactionChecker;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

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

        if (!$this->interactionChecker->checkColonyPosition($colony, $ship)) {
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
            /** @var ShipInterface $orbitShip */
            foreach ($fleet['ships'] as $orbitShip) {
                $wrapper = $this->shipWrapperFactory->wrapShip($orbitShip);
                if (!$wrapper->canBeRepaired() || $orbitShip->isUnderRepair()) {
                    continue;
                }
                foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($orbitShip->getRump()) as $rump_rel) {
                    if (array_key_exists($rump_rel->getBuildingFunction(), $fieldFunctions)) {
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

        $wrapper = $this->shipWrapperFactory->wrapShip($ship);
        if (!$wrapper->canBeRepaired()) {
            $game->addInformation(_('Das Schiff kann nicht repariert werden.'));
            return;
        }

        if ($ship->getState() == ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $game->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $obj = $this->colonyShipRepairRepository->prototype();
        $obj->setColony($colony);
        $obj->setShip($ship);
        $obj->setFieldId($field->getFieldId());
        $this->colonyShipRepairRepository->save($obj);

        $ship->setState(ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE);

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
        $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD);
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
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
