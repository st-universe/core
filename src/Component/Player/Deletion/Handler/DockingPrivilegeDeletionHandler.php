<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\User;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Component\Station\Dock\DockTypeEnum;

final class DockingPrivilegeDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository) {}

    #[\Override]
    public function delete(User $user): void
    {
        $this->dockingPrivilegeRepository->truncateByTypeAndTarget(DockTypeEnum::USER, $user->getId());
    }
}
