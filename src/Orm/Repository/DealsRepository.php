<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\DealsInterface;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Deals>
 */
final class DealsRepository extends EntityRepository implements DealsRepositoryInterface
{

    public function prototype(): DealsInterface
    {
        return new Deals();
    }

    public function save(DealsInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(DealsInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function hasActiveDeals(int $userId): bool
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(d.id) FROM %s d
                    WHERE d.start < :actime AND d.end > :actime
                    AND d.auction = FALSE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId
            ])
            ->getSingleScalarResult() > 0;
    }

    public function getActiveDealsGoods(int $userId): ?array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime
                    AND d.auction = FALSE AND d.want_prestige IS NULL
                    AND d.buildplan_id IS NULL AND d.ship = FALSE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveDealsShips(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime
                    AND d.auction = FALSE AND d.want_prestige IS NULL
                    AND d.buildplan_id > 0 AND d.ship = TRUE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveDealsBuildplans(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE
                    AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveDealsGoodsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime
                    AND d.auction = FALSE AND d.want_commodity IS NULL
                    AND d.buildplan_id IS NULL AND d.ship = FALSE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveDealsShipsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime
                     AND d.auction = FALSE AND d.want_commodity IS NULL
                     AND d.buildplan_id > 0 AND d.ship = TRUE
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveDealsBuildplansPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE
                     AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function hasActiveAuctions(int $userId): bool
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(d.id) FROM %s d WHERE d.start < :actime
                    AND d.end > :actime AND d.auction = TRUE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId
            ])
            ->getSingleScalarResult() > 0;
    }

    public function getActiveAuctionsGoods(int $userId): ?array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                     AND d.want_prestige IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveAuctionsShips(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                     AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveAuctionsBuildplans(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                    AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveAuctionsGoodsPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                    AND d.want_commodity IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveAuctionsShipsPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                    WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                    AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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

    public function getActiveAuctionsBuildplansPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d
                     WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                     AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL) ORDER BY d.id ASC',
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
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
                    Deals::class,
                    User::class
                )
            )
            ->setParameters([
                'actime' => time(),
                'userId' => $userId,
            ])
            ->getSingleScalarResult() > 0;
    }

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
                    AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
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
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
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
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
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
                      AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
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
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
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
                     AND (d.faction_id = (SELECT u.race FROM %s u WHERE u.id = :userId)
                        OR d.faction_id IS NULL)',
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

    public function getOwnEndedAuctionsGoods(int $userId): ?array
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
                    AND d.ship = FALSE',
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
                    AND d.ship = TRUE',
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
                    AND d.ship = FALSE',
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
                    AND d.ship = FALSE',
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
                    AND d.ship = TRUE',
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
                    AND d.ship = FALSE',
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
}
