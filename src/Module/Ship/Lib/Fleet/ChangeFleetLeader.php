<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use RuntimeException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ChangeFleetLeader implements ChangeFleetLeaderInterface
{
    private LoggerUtilInterface $logger;

    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private ShipRepositoryInterface $shipRepository,
        private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        private EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function change(Ship $oldLeader): void
    {
        $fleet = $oldLeader->getFleet();
        if ($fleet === null) {
            throw new RuntimeException('no fleet available');
        }

        /** @var false|Ship */
        $newLeader = current(
            array_filter(
                $fleet->getShips()->toArray(),
                fn(Ship $ship): bool => $ship !== $oldLeader
            )
        );

        if ($newLeader === false) {
            $this->cancelColonyBlockOrDefend->work(
                $oldLeader,
                new InformationWrapper()
            );
        } else {
            $newLeader->setIsFleetLeader(true);
            $this->shipRepository->save($newLeader);

            $this->logger->logf('new leader of fleetId %d now is shipId %d', $fleet->getId(), $newLeader->getId());

            $fleet->setLeadShip($newLeader);
            $this->fleetRepository->save($fleet);
            $this->entityManager->flush();
        }

        $fleet->getShips()->removeElement($oldLeader);

        $oldLeader->setFleet(null);
        $oldLeader->setIsFleetLeader(false);
        $this->shipRepository->save($oldLeader);

        if ($newLeader === false) {
            $this->logger->logf('now deleting fleet %d', $fleet->getId());
            $this->fleetRepository->delete($fleet);
        } else {
            $this->logger->logf('changed fleet leader of fleet %d', $fleet->getId());
        }
    }
}
