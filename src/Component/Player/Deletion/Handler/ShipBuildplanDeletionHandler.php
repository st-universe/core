<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

/**
 * Deletes ship buildplans on user deletion
 */
final class ShipBuildplanDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
    }

    public function delete(UserInterface $user): void
    {
        foreach ($this->shipBuildplanRepository->getByUser($user->getId()) as $shipBuildplan) {
            $this->shipBuildplanRepository->delete($shipBuildplan);
        }
    }
}
