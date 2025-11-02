<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\NPCQuestUser;

/**
 * @extends EntityRepository<NPCQuestUser>
 */
final class NPCQuestUserRepository extends EntityRepository implements NPCQuestUserRepositoryInterface
{
    #[\Override]
    public function prototype(): NPCQuestUser
    {
        return new NPCQuestUser();
    }

    #[\Override]
    public function save(NPCQuestUser $questUser): void
    {
        $em = $this->getEntityManager();

        $em->persist($questUser);
    }

    #[\Override]
    public function delete(NPCQuestUser $questUser): void
    {
        $em = $this->getEntityManager();

        $em->remove($questUser);
        $em->flush();
    }

    #[\Override]
    public function getByQuest(int $questId): array
    {
        return $this->findBy(
            ['quest_id' => $questId],
            ['id' => 'ASC']
        );
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
    public function getByQuestAndUser(int $questId, int $userId): ?NPCQuestUser
    {
        return $this->findOneBy([
            'quest_id' => $questId,
            'user_id' => $userId
        ]);
    }

    #[\Override]
    public function getByQuestAndMode(int $questId, int $mode): array
    {
        return $this->findBy([
            'quest_id' => $questId,
            'mode' => $mode
        ]);
    }

    #[\Override]
    public function getUnrewardedByQuest(int $questId): array
    {
        return $this->findBy([
            'quest_id' => $questId,
            'reward_received' => false
        ]);
    }

    #[\Override]
    public function truncateByQuest(int $questId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s qu WHERE qu.quest_id = :questId',
                    NPCQuestUser::class
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
                    'DELETE FROM %s qu WHERE qu.user_id = :userId',
                    NPCQuestUser::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }
}
