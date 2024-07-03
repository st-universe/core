<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Faction>
 */
final class FactionRepository extends EntityRepository implements FactionRepositoryInterface
{
    #[Override]
    public function getByChooseable(bool $chooseable): array
    {
        return $this->findBy([
            'chooseable' => $chooseable
        ]);
    }

    #[Override]
    public function getPlayableFactionsPlayerCount(): array
    {
        return $this
            ->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT
                        f as faction, COUNT(u.id) as count
                    FROM %s f INDEX BY f.id LEFT JOIN %s u WITH u.race = f.id AND u.id >= :firstUserId
                    WHERE f.chooseable = :chooseable
                    GROUP BY f.id
                    ORDER BY f.id',
                    Faction::class,
                    User::class
                )
            )
            ->setParameters([
                'chooseable' => true,
                'firstUserId' => UserEnum::USER_FIRST_ID
            ])
            ->getResult();
    }
}
