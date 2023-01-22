<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\GameTurnStatsInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipRump;

/**
 * @extends EntityRepository<GameTurnStats>
 */
final class GameTurnStatsRepository extends EntityRepository implements GameTurnStatsRepositoryInterface
{
    public function prototype(): GameTurnStatsInterface
    {
        return new GameTurnStats();
    }

    public function save(GameTurnStatsInterface $turn): void
    {
        $em = $this->getEntityManager();

        $em->persist($turn);
        $em->flush();
    }

    public function delete(GameTurnStatsInterface $turn): void
    {
        $em = $this->getEntityManager();

        $em->remove($turn);
    }

    public function getShipCount(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(s) FROM %s s
                WHERE s.user_id != :noOne',
                Ship::class
            )
        )->setParameter('noOne', GameEnum::USER_NOONE)
            ->getSingleScalarResult();
    }

    public function getShipCountManned(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(s) FROM %s s
                JOIN %s r WITH s.rumps_id = r.id
                WHERE r.base_crew <= (SELECT count(sc) FROM %s sc WHERE sc.ship_id = s.id)
                AND s.user_id != :noOne',
                Ship::class,
                ShipRump::class,
                ShipCrew::class
            )
        )->setParameter('noOne', GameEnum::USER_NOONE)
            ->getSingleScalarResult();
    }

    public function getShipCountNpc(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(s) FROM %s s
                WHERE s.user_id != :noOne
                AND s.user_id < :firstUserId',
                Ship::class
            )
        )->setParameters([
            'noOne' => GameEnum::USER_NOONE,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])
            ->getSingleScalarResult();
    }

    public function getFlightSigs24h(): int
    {
        return (int)((int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(fs) FROM %s fs
                WHERE fs.time > :threshold',
                FlightSignature::class
            )
        )->setParameter('threshold', time() - TimeConstants::ONE_DAY_IN_SECONDS)->getSingleScalarResult()) / 2;
    }

    public function getFlightSigsSystem24h(): int
    {
        return (int)((int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(fs) FROM %s fs
                WHERE fs.time > :threshold
                AND fs.starsystem_map_id IS NOT NULL',
                FlightSignature::class
            )
        )->setParameter('threshold', time() - TimeConstants::ONE_DAY_IN_SECONDS)->getSingleScalarResult()) / 2;
    }

    public function getLatestStats(int $amount): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT gts FROM %s gts
                ORDER BY gts.id DESC',
                GameTurnStats::class
            )
        )->setMaxResults($amount)->getResult();
    }
}
