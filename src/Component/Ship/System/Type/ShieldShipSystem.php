<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShieldShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipStateChangerInterface $shipStateChanger;

    public function __construct(
        ShipStateChangerInterface $shipStateChanger
    ) {
        $this->shipStateChanger = $shipStateChanger;
    }

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SHIELDS;
    }

    public function checkActivationConditions(ShipInterface $ship, ?string &$reason): bool
    {
        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($ship->isTractoring()) {
            $reason = _('der Traktorstrahl aktiviert ist');
            return false;
        }

        if ($ship->isTractored()) {
            $reason = _('das Schiff von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($ship->getShield() === 0) {
            $reason = _('die Schildemitter erschöpft sind');
            return false;
        }

        if ($ship->getLocation()->hasAnomaly(AnomalyTypeEnum::ANOMALY_TYPE_SUBSPACE_ELLIPSE)) {
            $reason = _('in diesem Sektor eine Subraumellipse vorhanden ist');
            return false;
        }

        return true;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $ship = $wrapper->get();
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);
        $ship->setDockedTo(null);
        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->setShield(0);
    }

    public function handleDamage(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->getShield() > $ship->getMaxShield()) {
            $ship->setShield($ship->getMaxShield());
        }
    }
}
