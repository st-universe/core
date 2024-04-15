<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class AstroLaboratoryShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const FINALIZING_ENERGY_COST = 15;

    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->astroEntryLib = $astroEntryLib;
    }

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY;
    }

    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiv ist');
            return false;
        }

        if (!$ship->getLss()) {
            $reason = _('die Langstreckensensoren nicht aktiv sind');
            return false;
        }

        if (!$ship->getNbs()) {
            $reason = _('die Nahbereichssensoren nicht aktiv sind');
            return false;
        }

        return true;
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }
    }
}
