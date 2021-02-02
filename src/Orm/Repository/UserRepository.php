<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\PlayerSetting\Lib\PlayerEnum;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInterface;

final class UserRepository extends EntityRepository implements UserRepositoryInterface
{

    public function prototype(): UserInterface
    {
        return new User();
    }

    public function save(UserInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    public function delete(UserInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getAmountByFaction(int $factionId): int
    {
        return $this->count([
            'race' => $factionId,
        ]);
    }

    public function getByResetToken(string $resetToken): ?UserInterface
    {
        return $this->findOneBy([
            'password_token' => $resetToken,
        ]);
    }

    public function getActualPlayer(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id > 100',
                User::class
            )
        )->getResult();
    }

    public function getDeleteable(
        int $idleTimeThreshold,
        int $idleTimeVacationThreshold,
        array $ignoreIds
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                 WHERE u.id > 100
                 AND u.id NOT IN (:ignoreIds)
                 AND u.delmark != :deletionForbidden
                 AND (u.delmark = :deletionMark
                        OR (u.vac_active = false AND u.lastaction > 0 AND u.lastaction < :idleTimeThreshold)
                        OR (u.vac_active = true AND u.lastaction > 0 AND u.lastaction < :idleTimeVacationThreshold)
                    )',
                User::class
            )
        )->setParameters([
            'idleTimeThreshold' => $idleTimeThreshold,
            'idleTimeVacationThreshold' => $idleTimeVacationThreshold,
            'ignoreIds' => $ignoreIds,
            'deletionMark' => PlayerEnum::DELETION_CONFIRMED,
            'deletionForbidden' => PlayerEnum::DELETION_FORBIDDEN
        ])->getResult();
    }

    public function getByEmail(string $email): ?UserInterface
    {
        return $this->findOneBy([
            'email' => $email
        ]);
    }

    public function getByLogin(string $loginName): ?UserInterface
    {
        return $this->findOneBy([
            'login' => $loginName
        ]);
    }

    public function getByAlliance(int $allianceId): iterable
    {
        return $this->findBy([
            'allys_id' => $allianceId
        ]);
    }

    public function getByMappingType(int $mappingType): iterable
    {
        return $this->findBy([
            'maptype' => $mappingType
        ]);
    }

    public function getList(
        string $sortField,
        string $sortOrder,
        int $limit,
        int $offset
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id > 100 ORDER BY u.%s %s',
                User::class,
                $sortField,
                $sortOrder
            )
        )
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();
    }

    public function getFriendsByUserAndAlliance(int $userId, int $allianceId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id IN (
                    SELECT cl.user_id FROM %s cl WHERE cl.mode = :mode AND cl.recipient = :userId
                ) OR (u.allys_id IS NOT NULL AND u.allys_id = :allianceId) AND u.id != :userId
                ORDER BY u.id',
                User::class,
                Contact::class
            )
        )->setParameters([
            'mode' => 1,
            'userId' => $userId,
            'allianceId' => $allianceId
        ])->getResult();
    }

    public function getOrderedByLastaction(int $limit, int $ignoreUserId, int $lastActionThreshold): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id != :ignoreUserId AND (u.show_online_status = :allowStart OR u.id IN (
                        SELECT cl.user_id FROM %s cl WHERE cl.mode = :contactListModeFriend AND cl.recipient = :ignoreUserId
                    )
                ) AND u.lastaction > :lastActionThreshold',
                User::class,
                Contact::class
            )
        )->setParameters([
            'ignoreUserId' => $ignoreUserId,
            'contactListModeFriend' => (string) ContactListModeEnum::CONTACT_FRIEND,
            'lastActionThreshold' => $lastActionThreshold,
            'allowStart' => 1
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getActiveAmount(): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u WHERE u.id > 100',
                User::class
            )
        )->getSingleScalarResult();
    }

    public function getActiveAmountRecentlyOnline(int $threshold): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u WHERE u.id > 100 AND u.lastaction > :threshold',
                User::class
            )
        )->setParameters([
            'threshold' => $threshold
        ])->getSingleScalarResult();
    }

    public function getNpcList(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id BETWEEN 10 AND 99 ORDER BY u.id',
                User::class
            )
        )->getResult();
    }

    public function getNonNpcList(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id > 100 ORDER BY u.id',
                User::class
            )
        )->getResult();
    }

    public function getLatinumTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT u.id as user_id, SUM(ss.count) + SUM(cs.count) + SUM(ts.count) + SUM(tro.amount * tro.gg_count) as amount
            FROM stu_user u
            LEFT JOIN stu_ships s
                ON u.id = s.user_id
            LEFT JOIN stu_ships_storage ss
                ON s.id = ss.ships_id
                AND ss.goods_id = :latId
            LEFT JOIN stu_colonies c
                ON u.id = c.user_id
            LEFT JOIN stu_colonies_storage cs
                ON c.id = cs.colonies_id
                AND cs.goods_id = :latId
            LEFT JOIN stu_trade_storage ts
                ON u.id = ts.user_id
                AND ts.goods_id = :latId
            LEFT JOIN stu_trade_offers tro
                ON u.id = tro.user_id
                AND tro.gg_id = :latId
            WHERE u.id > 100
            GROUP BY u.id
            ORDER BY amount DESC
            LIMIT 10',
            $rsm
        )->setParameters([
            'latId' => CommodityTypeEnum::GOOD_LATINUM
        ])->getResult();
    }
}
