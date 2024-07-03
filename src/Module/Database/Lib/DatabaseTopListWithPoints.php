<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopListWithPoints extends DatabaseTopList
{
    public function __construct(
        UserRepositoryInterface $userRepository,
        int $userId,
        private string $points,
        private ?int $time
    ) {
        parent::__construct(
            $userRepository,
            $userId
        );
    }

    public function getPoints(): string
    {
        return $this->points;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }
}
