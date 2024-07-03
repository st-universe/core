<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ShieldShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct(private ShipStateChangerInterface $shipStateChanger)
    {
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SHIELDS;
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

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

        if ($ship->getLocation()->hasAnomaly(AnomalyTypeEnum::SUBSPACE_ELLIPSE)) {
            $reason = _('in diesem Sektor eine Subraumellipse vorhanden ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $ship = $wrapper->get();
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);
        $ship->setDockedTo(null);
        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ON);
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->setShield(0);
    }

    #[Override]
    public function handleDamage(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->getShield() > $ship->getMaxShield()) {
            $ship->setShield($ship->getMaxShield());
        }
    }
}
