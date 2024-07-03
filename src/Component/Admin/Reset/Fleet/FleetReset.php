<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Fleet;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class FleetReset implements FleetResetInterface
{
    public function __construct(private FleetRepositoryInterface $fleetRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function deleteAllFleets(): void
    {
        echo "  - deleting all fleets\n";

        $this->entityManager->getConnection()->executeQuery('update stu_ships set fleets_id = null');

        $this->fleetRepository->truncateAllFleets();

        $this->entityManager->flush();
    }
}
