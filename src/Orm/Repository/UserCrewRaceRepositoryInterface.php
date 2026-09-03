<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserCrewRace;

/**
 * @extends ObjectRepository<UserCrewRace>
 */
interface UserCrewRaceRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserCrewRace;

    public function save(UserCrewRace $userCrewRace): void;

    public function exists(int $crewRaceId, int $userId): bool;

    /**
     * @return list<UserCrewRace>
     */
    public function getByUserId(int $userId): array;

    public function hasAnyForUserId(int $userId): bool;
}
