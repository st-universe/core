<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class ShipBuildplanDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        EntityManagerInterface  $entityManager
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->entityManager = $entityManager;
    }

    public function delete(UserInterface $user): void
    {
        $this->shipBuildplanRepository->truncateByUser($user->getId());
        $this->entityManager->flush();
    }
}
