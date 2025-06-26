<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRefererRepositoryInterface;

final class RefererDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private UserRefererRepositoryInterface $userRefererRepository) {}

    #[Override]
    public function delete(User $user): void
    {
        $userReferer = $user->getRegistration()->getReferer();

        if ($userReferer !== null) {
            $this->userRefererRepository->delete($userReferer);
        }
    }
}
