<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler\PreFlight;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\UpdateLocation\Handler\AbstractUpdateLocationHandler;
use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShipRepairHandler extends AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(LoggerUtilFactoryInterface $loggerUtilFactory)
    {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(ShipInterface $ship, ?ShipInterface $tractoringShip): void
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log(sprintf('     handle, shipState: %s', ShipStateEnum::getDescription($ship->getState())));
        if ($ship->cancelRepair()) {
            $this->loggerUtil->log('     canceledRepair');
            $this->addMessageInternal(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getName()));
        }
    }
}
