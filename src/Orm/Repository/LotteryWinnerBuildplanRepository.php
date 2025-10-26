<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\LotteryWinnerBuildplan;

/**
 * @extends EntityRepository<LotteryWinnerBuildplan>
 */
final class LotteryWinnerBuildplanRepository extends EntityRepository implements
    LotteryWinnerBuildplanRepositoryInterface
{
    #[\Override]
    public function prototype(): LotteryWinnerBuildplan
    {
        return new LotteryWinnerBuildplan();
    }

    #[\Override]
    public function save(LotteryWinnerBuildplan $lotteryWinnerBuildplan): void
    {
        $em = $this->getEntityManager();

        $em->persist($lotteryWinnerBuildplan);
    }

    /**
     * @return LotteryWinnerBuildplan[]
     */
    #[\Override]
    public function findByFactionId(?int $factionId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT lwb FROM %s lwb
                    WHERE lwb.faction_id IS NULL OR lwb.faction_id = :factionId',
                    LotteryWinnerBuildplan::class
                )
            )
            ->setParameter('factionId', $factionId)
            ->getResult();
    }
}
