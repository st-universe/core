<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<TradePost>
 */
final class TradePostRepository extends EntityRepository implements TradePostRepositoryInterface
{
    #[\Override]
    public function prototype(): TradePost
    {
        return new TradePost();
    }

    #[\Override]
    public function save(TradePost $tradePost): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradePost);
    }

    #[\Override]
    public function delete(TradePost $tradePost): void
    {
        $em = $this->getEntityManager();

        $em->remove($tradePost);
    }

    #[\Override]
    public function getByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp
                    JOIN %s u WITH tp.user = u
                    WHERE u.id = :userId',
                    TradePost::class,
                    User::class
                )
            )
            ->setParameters([
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[\Override]
    public function getByUserLicense(int $userId): array
    {
        $time = time();
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp WHERE tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime
                    ) ORDER BY tp.id ASC',
                    TradePost::class,
                    TradeLicense::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => $time
            ])
            ->getResult();
    }

    #[\Override]
    public function getByUserLicenseOnlyNPC(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp
                    JOIN %s u WITH tp.user = u
                    WHERE u.id < :firstUserId AND tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime
                    )',
                    TradePost::class,
                    User::class,
                    TradeLicense::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => time(),
                'firstUserId' => UserConstants::USER_FIRST_ID
            ])
            ->getResult();
    }

    #[\Override]
    public function getByUserLicenseOnlyFerg(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp
                    JOIN %s u WITH tp.user = u
                    WHERE u.id = 14 AND tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime AND tl.posts_id = :tradepostId
                    )',
                    TradePost::class,
                    User::class,
                    TradeLicense::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => time(),
                'tradepostId' => TradeEnum::DEALS_FERG_TRADEPOST_ID
            ])
            ->getResult();
    }
    #[\Override]
    public function getClosestTradePost(Location $location, User $user): ?TradePost
    {
        $layer = $location->getLayer();
        if ($layer === null) {
            return null;
        }

        $userAlliance = $user->getAlliance();
        $userFactionId = $user->getFactionId();

        try {
            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT tp
                        FROM %s tp
                        JOIN %s s WITH tp.station = s
                        JOIN %s l WITH s.location = l
                        WHERE l.layer = :layer
                        AND EXISTS (
                            SELECT dp.id FROM %s dp
                            WHERE dp.station = s
                            AND dp.privilege_mode = 1
                            AND (
                                (dp.privilege_type = 1 AND dp.target = :userId)
                                OR (dp.privilege_type = 2 AND dp.target = :allianceId)
                                OR (dp.privilege_type = 3 AND dp.target = :factionId)
                            )
                            AND NOT EXISTS (
                                SELECT dp2.id FROM %s dp2
                                WHERE dp2.station = s
                                AND dp2.privilege_mode = 2
                                AND (
                                    (dp2.privilege_type = 1 AND dp2.target = :userId)
                                    OR (dp2.privilege_type = 2 AND dp2.target = :allianceId)
                                    OR (dp2.privilege_type = 3 AND dp2.target = :factionId)
                                )
                            )
                        )
                        ORDER BY abs(l.cx - :cx) + abs(l.cy - :cy) ASC',
                        TradePost::class,
                        Station::class,
                        Location::class,
                        DockingPrivilege::class,
                        DockingPrivilege::class
                    )
                )
                ->setMaxResults(1)
                ->setParameters([
                    'layer' => $layer,
                    'cx' => $location->getCx(),
                    'cy' => $location->getCy(),
                    'userId' => $user->getId(),
                    'allianceId' => $userAlliance?->getId(),
                    'factionId' => $userFactionId
                ])
                ->getSingleResult();
        } catch (NoResultException) {
            return null;
        }
    }

    #[\Override]
    public function getUsersWithStorageOnTradepost(int $tradePostId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT u FROM %s u
                    WHERE EXISTS(
                            SELECT s.id FROM %s s
                            WHERE s.user_id = u.id
                            AND (s.tradepost_id = :tradePostId
                                OR s.tradeoffer_id IN (SELECT o.id FROM %s o WHERE o.posts_id = :tradePostId))
                            )',
                    User::class,
                    Storage::class,
                    TradeOffer::class
                )
            )
            ->setParameters([
                'tradePostId' => $tradePostId
            ])
            ->getResult();
    }

    #[\Override]
    public function truncateAllTradeposts(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s tp',
                TradePost::class
            )
        )->execute();
    }
}
