<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Fleet;

use Doctrine\ORM\EntityManagerInterface;
use Override;

final class FleetReset implements FleetResetInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function unsetShipFleetReference(): void
    {
        echo "  - removing all fleet references\n";

        $this->entityManager->getConnection()->executeQuery('update stu_ship set fleet_id = null');

        $this->entityManager->flush();
    }
}
