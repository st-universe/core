<?php

namespace Stu\Module\Maintenance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;

final class IdleUserDeletion implements MaintenanceHandlerInterface
{
    private PlayerDeletionInterface $playerDeletion;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PlayerDeletionInterface $playerDeletion,
        EntityManagerInterface $entityManager
    ) {
        $this->playerDeletion = $playerDeletion;
        $this->entityManager = $entityManager;
    }

    public function handle(): void
    {
        $this->playerDeletion->handleDeleteable();
        $this->entityManager->flush();
    }
}
