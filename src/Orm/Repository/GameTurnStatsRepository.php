<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\GameTurnStatsInterface;

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
    }

    public function delete(GameTurnStatsInterface $turn): void
    {
        $em = $this->getEntityManager();

        $em->remove($turn);
    }
}
