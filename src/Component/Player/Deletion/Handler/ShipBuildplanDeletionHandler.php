<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

/**
 * Deletes ship buildplans on user deletion
 */
final class ShipBuildplanDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private ShipBuildplanRepositoryInterface $shipBuildplanRepository)
    {
    }

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->shipBuildplanRepository->getByUser($user->getId()) as $shipBuildplan) {
            $this->shipBuildplanRepository->delete($shipBuildplan);
        }
    }
}
