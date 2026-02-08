<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\TimeConstants;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;

/**
 * @extends EntityRepository<GameTurnStats>
 */
final class GameTurnStatsRepository extends EntityRepository implements GameTurnStatsRepositoryInterface
{
    #[\Override]
    public function prototype(): GameTurnStats
    {
        return new GameTurnStats();
    }

    #[\Override]
    public function save(GameTurnStats $turn): void
    {
        $em = $this->getEntityManager();

        $em->persist($turn);
        $em->flush();
    }

    #[\Override]
    public function delete(GameTurnStats $turn): void
    {
        $em = $this->getEntityManager();

        $em->remove($turn);
    }

    #[\Override]
    public function getShipCount(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(s) FROM %s s
                WHERE s.user_id != :noOne',
                Ship::class
            )
        )->setParameter('noOne', UserConstants::USER_NOONE)
            ->getSingleScalarResult();
    }

    #[\Override]
    public function getShipCountManned(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(s) FROM %s s
                JOIN s.rump r
                JOIN r.baseValues rbv
                WHERE rbv.base_crew <= (SELECT count(c.id)
                                        FROM %s ca
                                        JOIN ca.crew c
                                        WHERE ca.spacecraft = s)
                AND s.user_id != :noOne',
                Spacecraft::class,
                CrewAssignment::class
            )
        )->setParameter('noOne', UserConstants::USER_NOONE)
            ->getSingleScalarResult();
    }

    #[\Override]
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
            'noOne' => UserConstants::USER_NOONE,
            'firstUserId' => UserConstants::USER_FIRST_ID
        ])
            ->getSingleScalarResult();
    }

    #[\Override]
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

    #[\Override]
    public function getFlightSigsSystem24h(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(fs) FROM %s fs
                JOIN %s sm
                WITH fs.location = sm
                WHERE fs.time > :threshold',
                FlightSignature::class,
                StarSystemMap::class
            )
        )->setParameter('threshold', time() - TimeConstants::ONE_DAY_IN_SECONDS)->getSingleScalarResult() / 2;
    }

    #[\Override]
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
