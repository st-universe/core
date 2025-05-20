<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Component\Station\Dock\DockTypeEnum;

final class DockingPrivilegeDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        $this->dockingPrivilegeRepository->truncateByTypeAndTarget(DockTypeEnum::USER, $user->getId());
    }
}
