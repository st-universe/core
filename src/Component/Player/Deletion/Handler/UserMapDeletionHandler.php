<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class UserMapDeletionHandler implements PlayerDeletionHandlerInterface
{
    private UserMapRepositoryInterface $userMapRepository;

    private UserLayerRepositoryInterface $userLayerRepository;

    public function __construct(
        UserMapRepositoryInterface $userMapRepository,
        UserLayerRepositoryInterface $userLayerRepository
    ) {
        $this->userMapRepository = $userMapRepository;
        $this->userLayerRepository = $userLayerRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->userMapRepository->truncateByUser($user->getId());
        $this->userLayerRepository->truncateByUser($user->getId());
    }
}
