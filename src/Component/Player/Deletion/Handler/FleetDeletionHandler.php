<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class FleetDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private FleetRepositoryInterface $fleetRepository)
    {
    }

    #[Override]
    public function delete(UserInterface $user): void
    {
        $this->fleetRepository->truncateByUser($user);
    }
}
