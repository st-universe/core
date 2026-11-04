<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;

final class SpecialResearchDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private SpecialResearchRepositoryInterface $repository) {}

    #[\Override]
    public function delete(User $user): void
    {
        $this->repository->deleteByUser($user);
    }

    public function detachColony(Colony $colony): void
    {
        $this->repository->detachColony($colony);
    }
}
