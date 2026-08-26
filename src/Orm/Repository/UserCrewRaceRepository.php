<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserCrewRace;

/**
 * @extends EntityRepository<UserCrewRace>
 */
final class UserCrewRaceRepository extends EntityRepository implements UserCrewRaceRepositoryInterface
{
    #[\Override]
    public function prototype(): UserCrewRace
    {
        return new UserCrewRace();
    }

    #[\Override]
    public function save(UserCrewRace $userCrewRace): void
    {
        $this->getEntityManager()->persist($userCrewRace);
    }

    #[\Override]
    public function exists(int $crewRaceId, int $userId): bool
    {
        return $this->count([
            'crewRace' => $crewRaceId,
            'user_id' => $userId
        ]) > 0;
    }

    #[\Override]
    public function getByUserId(int $userId): array
    {
        return $this->findBy(['user_id' => $userId]);
    }

    #[\Override]
    public function hasAnyForUserId(int $userId): bool
    {
        return $this->count(['user_id' => $userId]) > 0;
    }
}
