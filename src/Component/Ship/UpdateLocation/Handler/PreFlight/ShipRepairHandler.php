<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler\PreFlight;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\UpdateLocation\Handler\AbstractUpdateLocationHandler;
use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShipRepairHandler extends AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    private CancelRepairInterface $cancelRepair;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        CancelRepairInterface $cancelRepair,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->cancelRepair = $cancelRepair;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(ShipInterface $ship, ?ShipInterface $tractoringShip): void
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log(sprintf('     handle, shipState: %s', ShipStateEnum::getDescription($ship->getState())));
        if ($this->cancelRepair->cancelRepair($ship)) {
            $this->loggerUtil->log('     canceledRepair');
            $this->addMessageInternal(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getName()));
        }
    }
}
