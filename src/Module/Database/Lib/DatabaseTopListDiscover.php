<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopListDiscover extends DatabaseTopList
{
    /** @var array{user_id: int, points: int} */
    private array $entry;

    /**
     * @param array{user_id: int, points: int} $entry
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        array $entry
    ) {
        parent::__construct(
            $userRepository,
            $entry['user_id']
        );
        $this->entry = $entry;
    }

    public function getPoints(): int
    {
        return $this->entry['points'];
    }
}
