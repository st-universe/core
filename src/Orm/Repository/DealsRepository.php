<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Deals>
 */
final class DealsRepository extends EntityRepository implements DealsRepositoryInterface
{
    #[Override]
    public function prototype(): Deals
    {
        return new Deals();
    }

    #[Override]
    public function save(Deals $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(Deals $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[Override]
    public function hasActiveDeals(int $userId): bool
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(d.id) FROM %s d
                    WHERE d.start < :actime AND d.end > :actime
                    AND d.auction = FALSE
                    AND (d.faction = (SELECT f 
                                    FROM %s u 
                                    JOIN %s f
                                    WITH u.faction = f
                                    WHERE u.id = :userId)
                        OR d.faction IS NULL)',
                    Deals::class,
                    User::class,
                    Faction::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId
            ])
            ->getSingleScalarResult() > 0;
    }

    #[Override]
    public function getActiveDealsGoods(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime
                    AND d.auction = FALSE AND d.want_prestige IS NULL
                    AND d.buildplan_id IS NULL AND d.ship = FALSE
                    AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveDealsShips(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime
                    AND d.auction = FALSE AND d.want_prestige IS NULL
                    AND d.buildplan_id > 0 AND d.ship = TRUE
                    AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveDealsBuildplans(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE
                    AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                    AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveDealsGoodsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime
                    AND d.auction = FALSE AND d.want_commodity IS NULL
                    AND d.buildplan_id IS NULL AND d.ship = FALSE
                    AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveDealsShipsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime
                     AND d.auction = FALSE AND d.want_commodity IS NULL
                     AND d.buildplan_id > 0 AND d.ship = TRUE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveDealsBuildplansPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE
                     AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId
            ])
            ->getResult();
    }

    #[Override]
    public function hasActiveAuctions(int $userId): bool
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(d.id) FROM %s d WHERE d.start < :actime
                        AND d.end > :actime AND d.auction = TRUE
                        AND (d.faction = (SELECT f 
                                    FROM %s u 
                                    JOIN %s f
                                    WITH u.faction = f
                                    WHERE u.id = :userId)
                        OR d.faction IS NULL)',
                    Deals::class,
                    User::class,
                    Faction::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId
            ])
            ->getSingleScalarResult() > 0;
    }

    #[Override]
    public function getActiveAuctionsGoods(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                     AND d.want_prestige IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveAuctionsShips(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                     AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveAuctionsBuildplans(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                    AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                    AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveAuctionsGoodsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                    AND d.want_commodity IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE
                    AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveAuctionsShipsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                    AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE
                    AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getActiveAuctionsBuildplansPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                     AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.id ASC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function hasEndedAuctions(int $userId): bool
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(d.id) FROM %s d
                        WHERE d.end < :actime
                        AND (d.end + 2592000) > :actime
                        AND d.auction = TRUE
                        AND (d.auction_user != :userId OR d.taken_time IS NOT NULL)
                        AND (d.faction = (SELECT f 
                                        FROM %s u 
                                        JOIN %s f
                                        WITH u.faction = f
                                        WHERE u.id = :userId)
                        OR d.faction IS NULL)',
                    Deals::class,
                    User::class,
                    Faction::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getSingleScalarResult() > 0;
    }

    #[Override]
    public function hasOwnAuctionsToTake(int $userId): bool
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(d.id) FROM %s d
                    WHERE d.end BETWEEN :threshold AND :actime
                    AND d.taken_time IS NULL
                    AND d.auction = TRUE
                    AND d.auction_user = :userId',
                    Deals::class
                )
            )
            ->setParameters([
                'actime' => $time,
                'threshold' => $time - 30 * TimeConstants::ONE_DAY_IN_SECONDS,
                'userId' => $userId,
            ])
            ->getSingleScalarResult() > 0;
    }

    #[Override]
    public function getEndedAuctionsGoods(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.end < :actime
                    AND (d.end + 2592000) > :actime
                    AND (d.auction_user != :userId OR d.taken_time IS NOT NULL)
                    AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE
                    AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.end DESC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getEndedAuctionsShips(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.end < :actime
                     AND (d.end + 2592000) > :actime
                     AND (d.auction_user != :userId OR d.taken_time IS NOT NULL)
                     AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id > 0
                     AND d.ship = TRUE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.end DESC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getEndedAuctionsBuildplans(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.end < :actime AND (d.end + 2592000) > :actime
                     AND (d.auction_user != :userId OR d.taken_time IS NOT NULL)
                     AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id > 0
                     AND d.ship = FALSE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.end DESC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getEndedAuctionsGoodsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                      WHERE d.end < :actime AND (d.end + 2592000) > :actime
                      AND (d.auction_user != :userId OR d.taken_time IS NOT NULL)
                      AND d.auction = TRUE AND d.want_commodity IS NULL
                      AND d.buildplan_id IS NULL AND d.ship = FALSE
                      AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.end DESC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getEndedAuctionsShipsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.end < :actime AND (d.end + 2592000) > :actime
                     AND (d.auction_user != :userId OR d.taken_time IS NOT NULL)
                     AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id > 0
                     AND d.ship = TRUE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.end DESC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getEndedAuctionsBuildplansPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.end < :actime AND (d.end + 2592000) > :actime
                     AND (d.auction_user != :userId OR d.taken_time IS NOT NULL)
                     AND d.auction = TRUE AND d.want_commodity IS NULL
                     AND d.buildplan_id > 0 AND d.ship = FALSE
                     AND (d.faction = (SELECT u.faction FROM %s u WHERE u.id = :userId)
                        OR d.faction IS NULL) ORDER BY d.end DESC',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getOwnEndedAuctionsGoods(int $userId): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.end BETWEEN :threshold AND :actime
                    AND d.taken_time IS NULL
                    AND d.auction_user = :userId
                    AND d.auction = TRUE
                    AND d.want_prestige IS NULL
                    AND d.buildplan_id IS NULL
                    AND d.ship = FALSE ORDER BY d.end DESC',
                    Deals::class
                )
            )
            ->setParameters([
                'actime' => $time,
                'threshold' => $time - 30 * TimeConstants::ONE_DAY_IN_SECONDS,
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getOwnEndedAuctionsShips(int $userId): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.end BETWEEN :threshold AND :actime
                    AND d.taken_time IS NULL
                    AND d.auction_user = :userId
                    AND d.auction = TRUE
                    AND d.want_prestige IS NULL
                    AND d.buildplan_id > 0
                    AND d.ship = TRUE ORDER BY d.end DESC',
                    Deals::class
                )
            )
            ->setParameters([
                'actime' => $time,
                'threshold' => $time - 30 * TimeConstants::ONE_DAY_IN_SECONDS,
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getOwnEndedAuctionsBuildplans(int $userId): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.end BETWEEN :threshold AND :actime
                    AND d.taken_time IS NULL
                    AND d.auction_user = :userId
                    AND d.auction = TRUE
                    AND d.want_prestige IS NULL
                    AND d.buildplan_id > 0
                    AND d.ship = FALSE ORDER BY d.end DESC',
                    Deals::class
                )
            )
            ->setParameters([
                'actime' => $time,
                'threshold' => $time - 30 * TimeConstants::ONE_DAY_IN_SECONDS,
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getOwnEndedAuctionsGoodsPrestige(int $userId): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.end BETWEEN :threshold AND :actime
                    AND d.taken_time IS NULL
                    AND d.auction_user = :userId
                    AND d.auction = TRUE
                    AND d.want_commodity IS NULL
                    AND d.buildplan_id IS NULL
                    AND d.ship = FALSE ORDER BY d.end DESC',
                    Deals::class
                )
            )
            ->setParameters([
                'actime' => $time,
                'threshold' => $time - 30 * TimeConstants::ONE_DAY_IN_SECONDS,
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getOwnEndedAuctionsShipsPrestige(int $userId): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.end BETWEEN :threshold AND :actime
                    AND d.taken_time IS NULL
                    AND d.auction_user = :userId
                    AND d.auction = TRUE
                    AND d.want_commodity IS NULL
                    AND d.buildplan_id > 0
                    AND d.ship = TRUE ORDER BY d.end DESC',
                    Deals::class
                )
            )
            ->setParameters([
                'actime' => $time,
                'threshold' => $time - 30 * TimeConstants::ONE_DAY_IN_SECONDS,
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getOwnEndedAuctionsBuildplansPrestige(int $userId): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.end BETWEEN :threshold AND :actime
                    AND d.taken_time IS NULL
                    AND d.auction_user = :userId
                    AND d.auction = TRUE
                    AND d.want_commodity IS NULL
                    AND d.buildplan_id > 0
                    AND d.ship = FALSE ORDER BY d.end DESC',
                    Deals::class
                )
            )
            ->setParameters([
                'actime' => $time,
                'threshold' => $time - 30 * TimeConstants::ONE_DAY_IN_SECONDS,
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function truncateAllDeals(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s d',
                Deals::class
            )
        )->execute();
    }
}
