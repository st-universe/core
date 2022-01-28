<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Component\Game\GameEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class KnPostDeletionHandler implements PlayerDeletionHandlerInterface
{
    private KnPostRepositoryInterface $knPostRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        KnPostRepositoryInterface $knPostRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->knPostRepository = $knPostRepository;
        $this->userRepository = $userRepository;
    }

    public function delete(UserInterface $user): void
    {
        $gameFallbackUser = $this->userRepository->find(GameEnum::USER_NOONE);

        foreach ($this->knPostRepository->getByUser($user->getId()) as $obj) {
            $obj->setUsername($user->getUserName());
            $obj->setUser($gameFallbackUser);

            $this->knPostRepository->save($obj);
        }
    }
}
