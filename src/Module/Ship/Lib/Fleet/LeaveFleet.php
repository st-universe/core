<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LeaveFleet implements LeaveFleetInterface
{
    private LoggerUtilInterface $logger;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ChangeFleetLeaderInterface $changeFleetLeader,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    #[\Override]
    public function leaveFleet(Ship $ship): bool
    {
        $fleet = $ship->getFleet();

        if ($fleet === null) {
            return false;
        }

        $this->logger->logf('shipId %d leaving fleetId %d', $ship->getId(), $fleet->getId());

        if ($ship->isFleetLeader()) {
            $this->logger->logf('now changing fleet leader');
            $this->changeFleetLeader->change($ship);
        } else {
            $fleet->getShips()->removeElement($ship);

            $ship->setFleet(null);
            $ship->setIsFleetLeader(false);
            $ship->setFleetId(null);
        }

        $this->shipRepository->save($ship);

        return true;
    }
}
