<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Player\Deletion\PlayerDeletionInterface;

final class IdleUserDeletion implements MaintenanceHandlerInterface
{
    private PlayerDeletionInterface $playerDeletion;

    public function __construct(
        PlayerDeletionInterface $playerDeletion
    ) {
        $this->playerDeletion = $playerDeletion;
    }

    public function handle(): void
    {
        $this->playerDeletion->handleDeleteable();
    }
}
