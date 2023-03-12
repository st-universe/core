<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Updates the sending user to the fallback one on user deletion
 */
final class PrivateMessageDeletionHandler implements PlayerDeletionHandlerInterface
{
    private UserRepositoryInterface $userRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->userRepository = $userRepository;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function delete(UserInterface $user): void
    {
        $nobody = $this->userRepository->getFallbackUser();

        foreach ($this->privateMessageRepository->getBySender($user->getId()) as $pm) {
            $pm->setSender($nobody);

            $this->privateMessageRepository->save($pm);
        }
    }
}
