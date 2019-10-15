<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class ResearchDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $researchedRepository;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->researchedRepository = $researchedRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->researchedRepository->truncateForUser((int)$user->getId());
    }
}
