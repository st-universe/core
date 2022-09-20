<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\GameTurnStatsInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipRump;

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
                'SELECT count(s) FROM %s s',
                Ship::class
            )
        )->getSingleScalarResult();
    }

    public function getShipCountManned(): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(s) FROM %s s
                JOIN %s r ON s.rumps_id = r.id
                WHERE r.base_crew <= (SELECT count(sc) FROM %s sc WHERE sc.ships_id = s.id)',
                Ship::class,
                ShipRump::class,
                ShipCrew::class
            )
        )->getSingleScalarResult();
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
