<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\RpgPlotArchiv;
use Stu\Orm\Entity\RpgPlotArchivInterface;
use Stu\Orm\Entity\RpgPlotMemberArchiv;

/**
 * @extends EntityRepository<RpgPlotArchiv>
 */
final class RpgPlotArchivRepository extends EntityRepository implements RpgPlotArchivRepositoryInterface
{
    public function getByFoundingUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId,
        ]);
    }

    public function prototype(): RpgPlotArchivInterface
    {
        return new RpgPlotArchiv();
    }

    public function save(RpgPlotArchivInterface $rpgPlot): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlot);
    }

    public function delete(RpgPlotArchivInterface $rpgPlot): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlot);
    }

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

    public function getOrderedList(): array
    {
        return $this->findBy([], ['start_date' => 'asc']);
    }

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
