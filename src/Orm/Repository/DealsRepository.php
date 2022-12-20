<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\DealsInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\User;

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

    public function getDeals(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT d FROM %s d
                WHERE :time < d.end
                ORDER BY d.id ASC',
                Deals::class,
            )
        )->setParameters([
            'time' => time()
        ])->getResult();
    }

    public function getFergTradePost(
        int $tradePostId
    ): ?TradePostInterface {
        return $this->findOneBy([
            'id' => $tradePostId
        ]);
    }

    public function getActiveDeals(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE
                    ',
                    Deals::class,
                )
            )
            ->setParameters([
                'actime' => time(),
            ])
            ->getResult();
    }

    public function getActiveDealsGoods(int $userId): ?array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE AND d.want_prestige IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveDealsShips(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveDealsBuildplans(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveDealsGoodsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d  JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE AND d.want_commodity IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveDealsShipsPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveDealsBuildplansPrestige(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = FALSE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveAuctions(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE
                    ',
                    Deals::class,
                )
            )
            ->setParameters([
                'actime' => time(),
            ])
            ->getResult();
    }

    public function getActiveAuctionsGoods(int $userId): ?array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveAuctionsShips(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveAuctionsBuildplans(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveAuctionsGoodsPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d  JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveAuctionsShipsPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getActiveAuctionsBuildplansPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.start < :actime AND d.end > :actime AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getAnyEndedAuctions(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction = TRUE
                    ',
                    Deals::class,
                )
            )
            ->setParameters([
                'actime' => time(),
            ])
            ->getResult();
    }

    public function getEndedAuctions(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction = TRUE AND d.auction_user != :userid
                    ',
                    Deals::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getOwnEndedAuctions(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction = TRUE AND d.auction_user = :userid
                    ',
                    Deals::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getEndedAuctionsGoods(int $userId): ?array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user != :userid AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getEndedAuctionsShips(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user != :userid AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getEndedAuctionsBuildplans(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user != :userid AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getEndedAuctionsGoodsPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d  JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user != :userid AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getEndedAuctionsShipsPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user != :userid AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getEndedAuctionsBuildplansPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user != :userid AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                    ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getOwnEndedAuctionsGoods(int $userId): ?array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user = :userid AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                        ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getOwnEndedAuctionsShips(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user = :userid AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                        ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getOwnEndedAuctionsBuildplans(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user = :userid AND d.auction = TRUE AND d.want_prestige IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                        ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getOwnEndedAuctionsGoodsPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d  JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user = :userid AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id IS NULL AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                        ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getOwnEndedAuctionsShipsPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user = :userid AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = TRUE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                        ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }

    public function getOwnEndedAuctionsBuildplansPrestige(int $userId): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT d FROM %s d JOIN %s u WITH :userid = u.id WHERE d.end < :actime AND (d.end + 2592000) > :actime AND d.auction_user = :userid AND d.auction = TRUE AND d.want_commodity IS NULL AND d.buildplan_id > 0 AND d.ship = FALSE AND (d.faction_id = u.race OR d.faction_id IS NULL)
                        ',
                    Deals::class,
                    User::class,
                )
            )
            ->setParameters([
                'actime' => time(),
                'userid' => $userId,
            ])
            ->getResult();
    }
}
