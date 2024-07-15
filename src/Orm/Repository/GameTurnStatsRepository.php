<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\GameTurnStatsInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\StarSystemMap;

/**
 * @extends EntityRepository<GameTurnStats>
 */
final class GameTurnStatsRepository extends EntityRepository implements GameTurnStatsRepositoryInterface
{
    #[Override]
    public function prototype(): GameTurnStatsInterface
    {
        return new GameTurnStats();
    }

    #[Override]
    public function save(GameTurnStatsInterface $turn): void
    {
        $em = $this->getEntityManager();

        $em->persist($turn);
        $em->flush();
    }

    #[Override]
    public function delete(GameTurnStatsInterface $turn): void
    {
        $em = $this->getEntityManager();

        $em->remove($turn);
    }

    #[Override]
    public function getShipCount(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(s) FROM %s s
                WHERE s.user_id != :noOne',
                Ship::class
            )
        )->setParameter('noOne', UserEnum::USER_NOONE)
            ->getSingleScalarResult();
    }

    #[Override]
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
        )->setParameter('noOne', UserEnum::USER_NOONE)
            ->getSingleScalarResult();
    }

    #[Override]
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
            'noOne' => UserEnum::USER_NOONE,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])
            ->getSingleScalarResult();
    }

    #[Override]
    public function getFlightSigs24h(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(fs) FROM %s fs
                WHERE fs.time > :threshold',
                FlightSignature::class
            )
        )->setParameter('threshold', time() - TimeConstants::ONE_DAY_IN_SECONDS)->getSingleScalarResult() / 2;
    }

    #[Override]
    public function getFlightSigsSystem24h(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(fs) FROM %s fs
                JOIN %s sm
                WITH fs.location_id = sm.id
                WHERE fs.time > :threshold',
                FlightSignature::class,
                StarSystemMap::class
            )
        )->setParameter('threshold', time() - TimeConstants::ONE_DAY_IN_SECONDS)->getSingleScalarResult() / 2;
    }

    #[Override]
    public function getLatestStats(int $amount, int $divisor): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT gts FROM %s gts
                    WHERE MOD(gts.turn_id, :divisor) = 0
                    ORDER BY gts.id DESC',
                    GameTurnStats::class
                )
            )
            ->setMaxResults($amount)
            ->setParameter(':divisor', $divisor)
            ->getResult();
    }
}
