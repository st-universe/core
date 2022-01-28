<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class FleetDeletionHandler implements PlayerDeletionHandlerInterface
{
    private FleetRepositoryInterface $fleetRepository;

    public function __construct(
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->fleetRepository = $fleetRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->fleetRepository->truncateByUser($user);
    }
}
