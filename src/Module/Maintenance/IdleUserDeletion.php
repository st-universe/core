<?php

namespace Stu\Module\Maintenance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;

final class IdleUserDeletion implements MaintenanceHandlerInterface
{
    public function __construct(private PlayerDeletionInterface $playerDeletion, private EntityManagerInterface $entityManager) {}

    #[\Override]
    public function handle(): void
    {
        $this->playerDeletion->handleDeleteable();
        $this->entityManager->flush();
    }
}
