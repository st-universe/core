<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Override;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<TradePost>
 */
final class TradePostRepository extends EntityRepository implements TradePostRepositoryInterface
{
    #[Override]
    public function prototype(): TradePostInterface
    {
        return new TradePost();
    }

    #[Override]
    public function save(TradePostInterface $tradePost): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradePost);
    }

    #[Override]
    public function delete(TradePostInterface $tradePost): void
    {
        $em = $this->getEntityManager();

        $em->remove($tradePost);
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId]
        );
    }

    #[Override]
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

    #[Override]
    public function getByUserLicenseOnlyNPC(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp WHERE tp.user_id < :firstUserId AND tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime
                    )',
                    TradePost::class,
                    TradeLicense::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => time(),
                'firstUserId' => UserEnum::USER_FIRST_ID
            ])
            ->getResult();
    }

    #[Override]
    public function getByUserLicenseOnlyFerg(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp FROM %s tp WHERE tp.user_id = 14 AND tp.id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime AND tl.posts_id = :tradepostId
                    )',
                    TradePost::class,
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

    #[Override]
    public function getClosestNpcTradePost(LocationInterface $location): ?TradePostInterface
    {
        $layer = $location->getLayer();
        if ($layer === null) {
            return null;
        }

        try {

            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT tp
                            FROM %s tp
                            JOIN %s s
                            WITH tp.ship_id = s.id
                            JOIN %s l
                            WITH s.location_id = l.id
                            WHERE tp.user_id < :firstUserId
                            AND l.layer_id = :layerId
                            ORDER BY abs(l.cx - :cx) + abs(l.cy - :cy) ASC',
                        TradePost::class,
                        Ship::class,
                        Location::class
                    )
                )
                ->setMaxResults(1)
                ->setParameters([
                    'layerId' => $layer->getId(),
                    'cx' => $location->getCx(),
                    'cy' => $location->getCy(),
                    'firstUserId' => UserEnum::USER_FIRST_ID
                ])
                ->getSingleResult();
        } catch (NoResultException) {
            return null;
        }
    }

    #[Override]
    public function getFergTradePost(
        int $tradePostId
    ): ?TradePostInterface {
        return $this->findOneBy([
            'id' => $tradePostId
        ]);
    }

    #[Override]
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

    #[Override]
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
