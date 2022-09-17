<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\GameTurnStatsInterface;
use Stu\Orm\Entity\Ship;

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
}
