<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class UserMapDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private UserMapRepositoryInterface $userMapRepository, private UserLayerRepositoryInterface $userLayerRepository) {}

    #[\Override]
    public function delete(User $user): void
    {
        $this->userMapRepository->truncateByUser($user);

        foreach ($user->getUserLayers() as $userLayer) {
            $this->userLayerRepository->delete($userLayer);
        }
    }
}
