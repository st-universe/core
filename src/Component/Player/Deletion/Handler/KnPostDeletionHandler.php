<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Updates the associated user data for kn post items on user deletion
 */
final class KnPostDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private KnPostRepositoryInterface $knPostRepository, private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
    public function delete(UserInterface $user): void
    {
        $gameFallbackUser = $this->userRepository->getFallbackUser();

        foreach ($this->knPostRepository->getByUser($user->getId()) as $knPost) {
            $knPost->setUsername($user->getName());
            $knPost->setUser($gameFallbackUser);

            $this->knPostRepository->save($knPost);
        }
    }
}
