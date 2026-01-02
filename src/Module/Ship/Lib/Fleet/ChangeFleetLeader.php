<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class ChangeFleetLeader implements ChangeFleetLeaderInterface
{
    private LoggerUtilInterface $logger;

    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        private EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    #[\Override]
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

            $this->logger->logf('new leader of fleetId %d now is shipId %d', $fleet->getId(), $newLeader->getId());

            $fleet->setLeadShip($newLeader);
            $this->entityManager->flush();
        }

        $oldLeader->setFleet(null);
        $oldLeader->setIsFleetLeader(false);

        if ($newLeader === false) {
            $this->logger->logf('now deleting fleet %d', $fleet->getId());
            $this->fleetRepository->delete($fleet);
        } else {
            $this->logger->logf('changed fleet leader of fleet %d', $fleet->getId());
        }
    }
}
