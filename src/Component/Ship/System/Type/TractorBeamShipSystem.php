<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class TractorBeamShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipRepositoryInterface $shipRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipRepository = $shipRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($ship->getShieldState()) {
            $reason = _('die Schilde aktiviert sind');
            return false;
        }

        if ($ship->getDockedTo()) {
            $reason = _('das Schiff angedockt ist');
            return false;
        }

        if ($ship->traktorBeamToShip()) {
            $reason = sprintf(_('das Schiff selbst von dem Traktorstrahl der %s erfasst ist'), $ship->getTraktorShip()->getName());
            return false;
        }

        return true;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 2;
    }

    public function getEnergyConsumption(): int
    {
        return 2;
    }

    public function activate(ShipInterface $ship): void
    {
        if ($ship->getUser()->getId() === 126) {
            $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log('traktorOn');
        }
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        if ($ship->traktorBeamFromShip()) {
            $ship->deactivateTraktorBeam();
        }
    }
}
