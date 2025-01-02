<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRefererRepositoryInterface;

final class RefererDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private UserRefererRepositoryInterface $userRefererRepository) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        $entries = $this->userRefererRepository->getByUser($user);

        foreach ($entries as $entry) {
            $this->userRefererRepository->delete($entry);
        }
    }
}
