<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;

final class NotesDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $noteRepository;

    public function __construct(
        NoteRepositoryInterface $noteRepository
    ) {
        $this->noteRepository = $noteRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->noteRepository->truncateByUserId($user->getId());
    }
}
