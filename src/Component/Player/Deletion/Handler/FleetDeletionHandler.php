<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class FleetDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private FleetRepositoryInterface $fleetRepository)
    {
    }

    #[Override]
    public function delete(User $user): void
    {
        $this->fleetRepository->truncateByUser($user);
    }
}
