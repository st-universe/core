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
    public function delete(UserCrewRace $userCrewRace): void
    {
        $this->getEntityManager()->remove($userCrewRace);
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
        $userCrewRaces = $this->findBy(['user_id' => $userId]);
        foreach ($this->getEntityManager()->getUnitOfWork()->getIdentityMap()[UserCrewRace::class] ?? [] as $userCrewRace) {
            if ($userCrewRace instanceof UserCrewRace
                && $userCrewRace->getUserId() === $userId
                && !in_array($userCrewRace, $userCrewRaces, true)
            ) {
                $userCrewRaces[] = $userCrewRace;
            }
        }

        return $userCrewRaces;
    }

    #[\Override]
    public function hasAnyForUserId(int $userId): bool
    {
        return $this->count(['user_id' => $userId]) > 0;
    }
}
