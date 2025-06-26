<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\UserPirateRound;

/**
 * @extends EntityRepository<UserPirateRound>
 */
final class UserPirateRoundRepository extends EntityRepository implements UserPirateRoundRepositoryInterface
{
    #[Override]
    public function prototype(): UserPirateRound
    {
        return new UserPirateRound();
    }

    #[Override]
    public function save(UserPirateRound $userPirateRound): void
    {
        $em = $this->getEntityManager();

        $em->persist($userPirateRound);
    }

    #[Override]
    public function delete(UserPirateRound $userPirateRound): void
    {
        $em = $this->getEntityManager();

        $em->remove($userPirateRound);
    }

    #[Override]
    public function findByPirateRound(int $pirateRoundId): array
    {
        return $this->findBy(
            ['pirate_round_id' => $pirateRoundId],
            ['prestige' => 'DESC', 'destroyed_ships' => 'DESC']
        );
    }

    #[Override]
    public function findByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['pirate_round_id' => 'DESC']
        );
    }

    #[Override]
    public function findByUserAndPirateRound(int $userId, int $pirateRoundId): ?UserPirateRound
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'pirate_round_id' => $pirateRoundId
        ]);
    }
}
