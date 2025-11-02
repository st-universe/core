<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\NPCQuest;

/**
 * @extends EntityRepository<NPCQuest>
 */
final class NPCQuestRepository extends EntityRepository implements NPCQuestRepositoryInterface
{
    #[\Override]
    public function prototype(): NPCQuest
    {
        return new NPCQuest();
    }

    #[\Override]
    public function save(NPCQuest $quest): void
    {
        $em = $this->getEntityManager();

        $em->persist($quest);
    }

    #[\Override]
    public function delete(NPCQuest $quest): void
    {
        $em = $this->getEntityManager();

        $em->remove($quest);
        $em->flush();
    }

    #[\Override]
    public function getActiveQuests(): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT q FROM %s q
                    WHERE q.start <= :now
                    AND (q.end IS NULL OR q.end >= :now)
                    ORDER BY q.start DESC',
                    NPCQuest::class
                )
            )
            ->setParameters(['now' => $time])
            ->getResult();
    }

    #[\Override]
    public function getOpenForApplications(): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT q FROM %s q
                    WHERE q.start <= :now
                    AND q.application_end >= :now
                    AND (q.end IS NULL OR q.end >= :now)
                    ORDER BY q.application_end ASC',
                    NPCQuest::class
                )
            )
            ->setParameters(['now' => $time])
            ->getResult();
    }

    #[\Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['id' => 'DESC']
        );
    }

    #[\Override]
    public function getByPlot(int $plotId): array
    {
        return $this->findBy(
            ['plot_id' => $plotId],
            ['id' => 'DESC']
        );
    }

    #[\Override]
    public function getFinishedQuests(): array
    {
        $time = time();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT q FROM %s q
                    WHERE q.end IS NOT NULL
                    AND q.end < :now
                    ORDER BY q.end DESC',
                    NPCQuest::class
                )
            )
            ->setParameter('now', $time)
            ->getResult();
    }

    #[\Override]
    public function getActiveQuestsByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT q FROM %s q
                    WHERE q.user_id = :userId
                    AND q.end IS NULL
                    ORDER BY q.id DESC',
                    NPCQuest::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    #[\Override]
    public function getFinishedQuestsByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT q FROM %s q
                    WHERE q.user_id = :userId
                    AND q.end IS NOT NULL
                    ORDER BY q.id DESC',
                    NPCQuest::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    #[\Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s q WHERE q.user_id = :userId',
                    NPCQuest::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }
}
