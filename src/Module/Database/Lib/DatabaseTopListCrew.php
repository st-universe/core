<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Orm\Repository\UserRepositoryInterface;

class DatabaseTopListCrew extends DatabaseTopList
{
    /** @var array{user_id: int, factionid: int, crewc: int} */
    private array $entry;

    /**
     * @param array{user_id: int, factionid: int, crewc: int} $entry
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

    public function getCrewCount(): int
    {
        return $this->entry['crewc'];
    }

    public function getFaction(): int
    {
        return $this->entry['factionid'];
    }
}
