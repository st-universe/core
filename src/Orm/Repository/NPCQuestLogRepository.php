<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\NPCQuestLog;

/**
 * @extends EntityRepository<NPCQuestLog>
 */
final class NPCQuestLogRepository extends EntityRepository implements NPCQuestLogRepositoryInterface
{
    #[\Override]
    public function prototype(): NPCQuestLog
    {
        return new NPCQuestLog();
    }

    #[\Override]
    public function save(NPCQuestLog $log): void
    {
        $em = $this->getEntityManager();

        $em->persist($log);
    }

    #[\Override]
    public function delete(NPCQuestLog $log): void
    {
        $em = $this->getEntityManager();

        $em->remove($log);
        $em->flush();
    }

    #[\Override]
    public function getByQuest(int $questId): array
    {
        return $this->findBy(
            ['quest_id' => $questId],
            ['date' => 'DESC']
        );
    }

    #[\Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['date' => 'DESC']
        );
    }

    #[\Override]
    public function getByQuestAndUser(int $questId, int $userId): array
    {
        return $this->findBy(
            [
                'quest_id' => $questId,
                'user_id' => $userId
            ],
            ['date' => 'DESC']
        );
    }

    #[\Override]
    public function getActiveByQuest(int $questId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT l FROM %s l
                    WHERE l.quest_id = :questId
                    AND l.deleted IS NULL
                    ORDER BY l.date DESC',
                    NPCQuestLog::class
                )
            )
            ->setParameter('questId', $questId)
            ->getResult();
    }

    #[\Override]
    public function getActiveByQuestAndUser(int $questId, int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT l FROM %s l
                    WHERE l.quest_id = :questId
                    AND l.user_id = :userId
                    AND l.deleted IS NULL
                    ORDER BY l.date DESC',
                    NPCQuestLog::class
                )
            )
            ->setParameters([
                'questId' => $questId,
                'userId' => $userId
            ])
            ->getResult();
    }

    #[\Override]
    public function truncateByQuest(int $questId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s l WHERE l.quest_id = :questId',
                    NPCQuestLog::class
                )
            )
            ->setParameter('questId', $questId)
            ->execute();
    }

    #[\Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s l WHERE l.user_id = :userId',
                    NPCQuestLog::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }
}
