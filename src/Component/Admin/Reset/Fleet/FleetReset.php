<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Fleet;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class FleetReset implements FleetResetInterface
{
    private FleetRepositoryInterface $fleetRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->entityManager = $entityManager;
    }

    public function deleteAllFleets(): void
    {
        echo "  - deleting all fleets\n";

        $this->entityManager->getConnection()->executeQuery('update stu_ships set fleets_id = null');

        $this->fleetRepository->truncateAllFleets();

        $this->entityManager->flush();
    }
}
