<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\LotteryWinnerBuildplan;
use Stu\Orm\Entity\LotteryWinnerBuildplanInterface;

/**
 * @extends EntityRepository<LotteryWinnerBuildplan>
 */
final class LotteryWinnerBuildplanRepository extends EntityRepository implements LotteryWinnerBuildplanRepositoryInterface
{
    #[Override]
    public function prototype(): LotteryWinnerBuildplanInterface
    {
        return new LotteryWinnerBuildplan();
    }

    #[Override]
    public function save(LotteryWinnerBuildplanInterface $lotteryWinnerBuildplan): void
    {
        $em = $this->getEntityManager();

        $em->persist($lotteryWinnerBuildplan);
    }

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
