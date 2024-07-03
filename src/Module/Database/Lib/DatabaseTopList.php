<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

abstract class DatabaseTopList
{
    public function __construct(private UserRepositoryInterface $userRepository, private int $user_id)
    {
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
