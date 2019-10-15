<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class KnCommentDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $knCommentRepository;

    public function __construct(
        KnCommentRepositoryInterface $knCommentRepository
    ) {
        $this->knCommentRepository = $knCommentRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->knCommentRepository->truncateByUser($user->getId());
    }
}
