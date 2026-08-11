<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserCrewRank;

/**
 * @extends EntityRepository<UserCrewRank>
 */
final class UserCrewRankRepository extends EntityRepository implements UserCrewRankRepositoryInterface
{
    #[\Override]
    public function prototype(): UserCrewRank
    {
        return new UserCrewRank();
    }

    #[\Override]
    public function save(UserCrewRank $userCrewRank): void
    {
        $this->getEntityManager()->persist($userCrewRank);
    }

    #[\Override]
    public function delete(UserCrewRank $userCrewRank): void
    {
        $this->getEntityManager()->remove($userCrewRank);
    }

    #[\Override]
    public function getByUserAndRank(User $user, CrewSkillLevelEnum $rank): ?UserCrewRank
    {
        return $this->findOneBy([
            'user' => $user,
            'rank' => $rank
        ]);
    }

    #[\Override]
    public function getRankName(User $user, CrewSkillLevelEnum $rank): string
    {
        return $this->getCustomRankName($user, $rank) ?: $rank->getDescription($user->getFactionId());
    }

    #[\Override]
    public function getCustomRankName(User $user, CrewSkillLevelEnum $rank): string
    {
        return $this->getByUserAndRank($user, $rank)?->getName() ?? '';
    }

    #[\Override]
    public function truncateByUser(User $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ucr WHERE ucr.user = :user',
                UserCrewRank::class
            )
        )->setParameter('user', $user)->execute();
    }
}
