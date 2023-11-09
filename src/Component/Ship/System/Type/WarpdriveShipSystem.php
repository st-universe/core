<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use RuntimeException;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class WarpdriveShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipStateChangerInterface $shipStateChanger;

    private ShipUndockingInterface $shipUndocking;

    public function __construct(
        ShipStateChangerInterface $shipStateChanger,
        ShipUndockingInterface $shipUndocking
    ) {
        $this->shipStateChanger = $shipStateChanger;
        $this->shipUndocking = $shipUndocking;
    }

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_WARPDRIVE;
    }

    public function checkActivationConditions(ShipWrapperInterface $wrapper, ?string &$reason): bool
    {
        $ship = $wrapper->get();

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

        $reactor = $wrapper->getReactorWrapper();
        if (
            $reactor === null
            || $reactor->get()->getSystemType() === ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR
        ) {
            $reason = _('kein leistungsstarker Reaktor installiert ist');
            return false;
        }

        if (!$reactor->isHealthy()) {
            $reason = sprintf(_('der %s zerstört ist'), $reactor->get()->getSystemType()->getDescription());
            return false;
        }

        return true;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $ship = $wrapper->get();
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);
        $this->shipUndocking->undockAllDocked($ship);
        $ship->setDockedTo(null);
        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ON);

        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
        if ($tractoredShipWrapper !== null) {
            $this->shipStateChanger->changeShipState($tractoredShipWrapper, ShipStateEnum::SHIP_STATE_NONE);
        }
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
