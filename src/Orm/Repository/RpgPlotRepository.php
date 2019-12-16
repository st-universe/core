<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\UserInterface;

final class RpgPlotRepository extends EntityRepository implements RpgPlotRepositoryInterface
{
    public function getByFoundingUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId,
        ]);
    }

    public function prototype(): RpgPlotInterface
    {
        return new RpgPlot();
    }

    public function save(RpgPlotInterface $rpgPlot): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlot);
        $em->flush();
    }

    public function delete(RpgPlotInterface $rpgPlot): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlot);
        $em->flush();
    }

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

    public function getByUser(UserInterface $user): array
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

    public function getOrderedList(): array
    {
       return $this->findBy([], ['start_date' => 'asc']);
    }
}
