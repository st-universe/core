<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LeaveFleet implements LeaveFleetInterface
{
    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function leaveFleet(ShipInterface $ship): bool
    {
        $this->loggerUtil->log('leaveFleet');

        $fleet = $ship->getFleet();

        if ($fleet === null) {
            return false;
        }

        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader($ship);
        } else {
            $fleet->getShips()->removeElement($ship);

            $ship->setFleet(null);
            $ship->setIsFleetLeader(false);
            $ship->setFleetId(null);
        }

        $this->shipRepository->save($ship);
        return true;
    }

    private function changeFleetLeader(ShipInterface $oldLeader): void
    {
        $this->loggerUtil->log('changeFleetLeader');

        $ship = current(
            array_filter(
                $oldLeader->getFleet()->getShips()->toArray(),
                function (ShipInterface $ship) use ($oldLeader): bool {
                    return $ship !== $oldLeader;
                }
            )
        );

        if (!$ship) {
            $this->cancelColonyBlockOrDefend->work($oldLeader);
        }

        $fleet = $oldLeader->getFleet();

        $oldLeader->setFleet(null);
        $oldLeader->setIsFleetLeader(false);
        $fleet->getShips()->removeElement($oldLeader);

        $this->shipRepository->save($oldLeader);

        if (!$ship) {
            $this->loggerUtil->log('noFollowUp');
            $this->fleetRepository->delete($fleet);

            return;
        }
        $fleet->setLeadShip($ship);
        $ship->setIsFleetLeader(true);

        $this->fleetRepository->save($fleet);

        $this->loggerUtil->log('setNewLeader');
    }
}
