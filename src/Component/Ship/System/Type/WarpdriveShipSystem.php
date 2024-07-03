<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
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
    public function __construct(private ShipStateChangerInterface $shipStateChanger, private ShipUndockingInterface $shipUndocking)
    {
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_WARPDRIVE;
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
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
        if ($reactor === null) {
            throw new RuntimeException('this should not happen, warpdrive should only be installed with potent reactor');
        }

        if (!$reactor->isHealthy()) {
            $reason = sprintf(_('der %s zerstört ist'), $reactor->get()->getSystemType()->getDescription());
            return false;
        }

        return true;
    }

    #[Override]
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

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $systemData->setWarpDrive(0)->update();
    }

    #[Override]
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
