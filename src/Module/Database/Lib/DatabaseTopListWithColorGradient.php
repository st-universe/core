<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopListWithColorGradient extends DatabaseTopList
{
    public function __construct(
        UserRepositoryInterface $userRepository,
        int $userId,
        private string $gradientColor
    ) {
        parent::__construct($userRepository, $userId);
    }

    public function getGradientColor(): string
    {
        return $this->gradientColor;
    }
}
