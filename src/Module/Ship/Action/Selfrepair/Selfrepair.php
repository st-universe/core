<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Selfrepair;

use request;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\Selfrepair\SelfrepairUtilInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Station\View\ShowShipRepair\ShowShipRepair;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class Selfrepair implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SELF_REPAIR';

    private ShipLoaderInterface $shipLoader;

    private SelfrepairUtilInterface $selfrepairUtil;

    private ShipRepositoryInterface $shipRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        SelfrepairUtilInterface $selfrepairUtil,
        ShipRepositoryInterface $shipRepository,
        ShipStorageManagerInterface $shipStorageManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->selfrepairUtil = $selfrepairUtil;
        $this->shipRepository = $shipRepository;
        $this->shipStorageManager = $shipStorageManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipRepair::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(request::postIntFatal('id'), $userId);

        $repairType = request::postIntFatal('partschoice');

        if ($repairType < RepairTaskEnum::SPARE_PARTS_ONLY || $repairType > RepairTaskEnum::BOTH) {
            return;
        }

        $repairOptions = $this->selfrepairUtil->determineRepairOptions($ship);

        if (!array_key_exists(request::postIntFatal('sid'), $repairOptions)) {
            return;
        }

        $systemType = request::postIntFatal('sid');

        $isInstantRepair = request::postString('instantrepair');

        if (!$ship->hasEnoughCrew()) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (
            $ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE
            || $ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE
        ) {
            $game->addInformation(_('Das Schiff wird bereits repariert.'));
            return;
        }

        if ($ship->getState() == ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $game->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $neededSparePartCount = (int) $ship->getMaxHuell() / 150;

        if (!$this->checkForSpareParts($ship, $neededSparePartCount, $repairType, $game)) {
            return;
        }

        $this->consumeGoods($ship, $repairType, $neededSparePartCount);

        if (!$isInstantRepair) {
            $ship->setState(ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE);

            $freeEngineerCount = $this->selfrepairUtil->determineFreeEngineerCount($ship);
            $duration = RepairTaskEnum::STANDARD_REPAIR_DURATION * (1 - $freeEngineerCount / 10);

            $this->selfrepairUtil->createRepairTask($ship, $systemType, $repairType, time() + (int) $duration);
            $game->addInformationf(_('Das Schiffssystem %s wird repariert. Fertigstellung %s'), ShipSystemTypeEnum::getDescription($systemType), date("d.m.Y H:i", (time() + (int) $duration)));
        } else {
            $this->selfrepairUtil->instantSelfRepair($ship, $systemType, $repairType);
            $game->addInformationf(_('Das Schiffssystem %s wurde sofort repariert.'), ShipSystemTypeEnum::getDescription($systemType));
        }

        $this->shipRepository->save($ship);
    }

    private function checkForSpareParts(ShipInterface $ship, int $neededSparePartCount, int $repairType, GameControllerInterface $game): bool
    {
        $result = true;

        if (
            $repairType === RepairTaskEnum::SPARE_PARTS_ONLY || $repairType === RepairTaskEnum::BOTH
            && (!$ship->getStorage()->containsKey(CommodityTypeEnum::GOOD_SPARE_PART)
                || $ship->getStorage()->get(CommodityTypeEnum::GOOD_SPARE_PART)->getAmount() < $neededSparePartCount)
        ) {
            $game->addInformationf(_('Für die Reparatur werden %d Ersatzteile benötigt'), $neededSparePartCount);
            $result = false;
        }

        if (
            $repairType === RepairTaskEnum::SYSTEM_COMPONENTS_ONLY || $repairType === RepairTaskEnum::BOTH
            && (!$ship->getStorage()->containsKey(CommodityTypeEnum::GOOD_SYSTEM_COMPONENT)
                || $ship->getStorage()->get(CommodityTypeEnum::GOOD_SYSTEM_COMPONENT)->getAmount() < $neededSparePartCount)
        ) {
            $game->addInformationf(_('Für die Reparatur werden %d Systemkomponenten benötigt'), $neededSparePartCount);
            $result = false;
        }

        return $result;
    }

    private function consumeGoods(ShipInterface $ship, int $repairType, $neededSparePartCount): void
    {
        if (
            $repairType === RepairTaskEnum::SPARE_PARTS_ONLY
            || $repairType === RepairTaskEnum::BOTH
        ) {
            $commodity = $ship->getStorage()->get(CommodityTypeEnum::GOOD_SPARE_PART);
            $this->shipStorageManager->lowerStorage($ship, $commodity, $neededSparePartCount);
        }

        if (
            $repairType === RepairTaskEnum::SYSTEM_COMPONENTS_ONLY
            || $repairType === RepairTaskEnum::BOTH
        ) {
            $commodity = $ship->getStorage()->get(CommodityTypeEnum::GOOD_SYSTEM_COMPONENT);
            $this->shipStorageManager->lowerStorage($ship, $commodity, $neededSparePartCount);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
