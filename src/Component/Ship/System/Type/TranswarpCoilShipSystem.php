<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class TranswarpCoilShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipRepositoryInterface $shipRepository;

    private CancelRepairInterface $cancelRepair;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        CancelRepairInterface $cancelRepair
    ) {
        $this->shipRepository = $shipRepository;
        $this->cancelRepair = $cancelRepair;
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->isTractored()) {
            $reason = _('es von einem Traktorstrahl gehalten wird');
            return false;
        }

        return true;
    }

    public function getCooldownSeconds(): ?int
    {
        return 300;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 55;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $ship = $wrapper->get();
        $this->cancelRepair->cancelRepair($ship);
        $this->undock($ship);
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL)->setMode(ShipSystemModeEnum::MODE_ON);
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

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL)->setMode(ShipSystemModeEnum::MODE_OFF);
    }
}
