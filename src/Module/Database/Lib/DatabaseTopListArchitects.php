<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopListArchitects extends DatabaseTopList
{
    private string $points;

    public function __construct(
        UserRepositoryInterface $userRepository,
        int $userId,
        string $points
    ) {
        parent::__construct(
            $userRepository,
            $userId
        );
        $this->points = $points;
    }

    public function getPoints(): string
    {
        return $this->points;
    }
}
