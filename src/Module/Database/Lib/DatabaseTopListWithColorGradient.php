<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopListWithColorGradient extends DatabaseTopList
{
    private string $gradientColor;

    public function __construct(
        UserRepositoryInterface $userRepository,
        int $userId,
        string $gradientColor
    ) {
        parent::__construct($userRepository, $userId);
        $this->gradientColor = $gradientColor;
    }

    public function getGradientColor(): string
    {
        return $this->gradientColor;
    }
}
