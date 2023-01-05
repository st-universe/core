<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class WarpdriveShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipStateChangerInterface $shipStateChanger;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipStateChangerInterface $shipStateChanger
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipStateChanger = $shipStateChanger;
    }

    public function getSystemType(): int
    {
        return ShipSystemTypeEnum::SYSTEM_WARPDRIVE;
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->isTractored()) {
            $reason = _('es von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($ship->getHoldingWeb() && $ship->getHoldingWeb()->isFinished()) {
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

        if ($ship->isTractoring()) {
            $eps = $wrapper->getEpsSystemData();
            if ($eps->getEps() > $this->getEnergyUsageForActivation()) {
                $this->shipStateChanger->changeShipState($wrapper->getTractoredShipWrapper(), ShipStateEnum::SHIP_STATE_NONE);

                $eps->setEps($eps->getEps() - $this->getEnergyUsageForActivation())->update();
            } else {
                $manager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation
            }
        }
    }

    private function undock(ShipInterface $ship): void
    {
        $ship->setDockedTo(null);
        foreach ($ship->getDockedShips() as $dockedShip) {
            $dockedShip->setDockedTo(null);
            $this->shipRepository->save($dockedShip);
        }
        $ship->getDockedShips()->clear();
    }
}
