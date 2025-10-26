<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<RpgPlot>
 */
final class RpgPlotRepository extends EntityRepository implements RpgPlotRepositoryInterface
{
    #[\Override]
    public function getByFoundingUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId,
        ]);
    }

    #[\Override]
    public function prototype(): RpgPlot
    {
        return new RpgPlot();
    }

    #[\Override]
    public function save(RpgPlot $rpgPlot): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlot);
    }

    #[\Override]
    public function delete(RpgPlot $rpgPlot): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlot);
    }

    #[\Override]
    public function getActiveByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT p FROM %s p WHERE p.end_date IS NULL AND p.id IN (
                        SELECT pm.plot_id FROM %s pm WHERE pm.user_id = :userId
                    ) ORDER BY p.start_date DESC',
                    RpgPlot::class,
                    RpgPlotMember::class
                )
            )
            ->setParameters(['userId' => $userId])
            ->getResult();
    }

    #[\Override]
    public function getByUser(User $user): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT p FROM %s p WHERE p.id IN (
                        SELECT pm.plot_id FROM %s pm WHERE pm.user_id = :userId
                    ) ORDER BY p.start_date DESC',
                    RpgPlot::class,
                    RpgPlotMember::class
                )
            )
            ->setParameters(['userId' => $user])
            ->getResult();
    }

    #[\Override]
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
                    RpgPlot::class,
                    KnPost::class
                )
            )
            ->setParameters(['deletionThreshold' => time() - $maxAge])
            ->getResult();
    }

    #[\Override]
    public function getOrderedList(): array
    {
        return $this->findBy([], ['start_date' => 'asc']);
    }
}
