<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopListWithPoints extends DatabaseTopList
{
    private string $points;

    private ?int $time;

    public function __construct(
        UserRepositoryInterface $userRepository,
        int $userId,
        string $points,
        ?int $time
    ) {
        parent::__construct(
            $userRepository,
            $userId
        );
        $this->points = $points;
        $this->time = $time;
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
