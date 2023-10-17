<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use RuntimeException;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class WarpdriveShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipStateChangerInterface $shipStateChanger;

    private ShipTakeoverManagerInterface $shipTakeoverManager;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipStateChangerInterface $shipStateChanger,
        ShipTakeoverManagerInterface $shipTakeoverManager
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipStateChanger = $shipStateChanger;
        $this->shipTakeoverManager = $shipTakeoverManager;
    }

    public function getSystemType(): int
    {
        return ShipSystemTypeEnum::SYSTEM_WARPDRIVE;
    }

    public function checkActivationConditions(ShipInterface $ship, ?string &$reason): bool
    {
        if ($ship->isTractored()) {
            $reason = _('es von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($ship->getHoldingWeb() !== null && $ship->getHoldingWeb()->isFinished()) {
            $reason = _('es in einem Energienetz gefangen ist');
            return false;
        }

        if ($ship->getSystem() !== null && $ship->getSystem()->isWormhole()) {
            $reason = _('es sich in einem Wurmloch befindet');
            return false;
        }

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_WARPCORE)) {
            $reason = _('der Warpkern zerstört ist');
            return false;
        }

        return true;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $ship = $wrapper->get();
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);
        $this->undock($ship);
        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ON);

        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
        if ($tractoredShipWrapper !== null) {
            $this->shipStateChanger->changeShipState($tractoredShipWrapper, ShipStateEnum::SHIP_STATE_NONE);
        }

        $this->shipTakeoverManager->cancelTakeover($ship->getTakeoverActive());
    }

    private function undock(ShipInterface $ship): void
    {
        //TODO undock component with PM to docked ships
        //search elsewhere
        $ship->setDockedTo(null);
        foreach ($ship->getDockedShips() as $dockedShip) {
            $dockedShip->setDockedTo(null);
            $this->shipRepository->save($dockedShip);
        }
        $ship->getDockedShips()->clear();
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $systemData->setWarpDrive(0)->update();
    }

    public function handleDamage(ShipWrapperInterface $wrapper): void
    {
        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($systemData->getWarpDrive() > $systemData->getMaxWarpDrive()) {
            $systemData->setWarpDrive($systemData->getMaxWarpDrive())->update();
        }
    }
}
