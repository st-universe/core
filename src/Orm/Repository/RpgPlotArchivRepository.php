<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\RpgPlotArchiv;
use Stu\Orm\Entity\RpgPlotMemberArchiv;

/**
 * @extends EntityRepository<RpgPlotArchiv>
 */
final class RpgPlotArchivRepository extends EntityRepository implements RpgPlotArchivRepositoryInterface
{
    #[Override]
    public function getByFoundingUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId,
        ]);
    }

    #[Override]
    public function prototype(): RpgPlotArchiv
    {
        return new RpgPlotArchiv();
    }

    #[Override]
    public function save(RpgPlotArchiv $rpgPlot): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlot);
    }

    #[Override]
    public function delete(RpgPlotArchiv $rpgPlot): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlot);
    }

    #[Override]
    public function getActiveByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT p FROM %s p WHERE p.end_date IS NULL AND p.id IN (
                        SELECT pm.plot_id FROM %s pm WHERE pm.user_id = :userId
                    ) ORDER BY p.start_date DESC',
                    RpgPlotArchiv::class,
                    RpgPlotMemberArchiv::class
                )
            )
            ->setParameters(['userId' => $userId])
            ->getResult();
    }

    #[Override]
    public function getEmptyOldPlots(int $maxAge): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT pl
                    FROM %s pl
                    WHERE NOT EXISTS (SELECT kn.id
                                    FROM %s kn
                                    WHERE kn.plot_id = pl.id)
                    AND pl.start_date < :deletionThreshold',
                    RpgPlotArchiv::class,
                    KnPostArchiv::class
                )
            )
            ->setParameters(['deletionThreshold' => time() - $maxAge])
            ->getResult();
    }

    #[Override]
    public function getOrderedList(): array
    {
        return $this->findBy([], ['start_date' => 'asc']);
    }

    #[Override]
    public function truncateAllEntities(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s rp',
                RpgPlotArchiv::class
            )
        )->execute();
    }
}
