<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

abstract class DatabaseTopList
{
    private int $user_id;

    private UserRepositoryInterface $userRepository;

    function __construct(
        UserRepositoryInterface $userRepository,
        int $user_id
    ) {
        $this->user_id = $user_id;
        $this->userRepository = $userRepository;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): ?UserInterface
    {
        return $this->userRepository->find($this->user_id);
    }
}
