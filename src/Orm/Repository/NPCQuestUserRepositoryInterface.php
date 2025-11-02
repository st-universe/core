<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\NPCQuestUser;

/**
 * @extends ObjectRepository<NPCQuestUser>
 *
 * @method null|NPCQuestUser find(integer $id)
 */
interface NPCQuestUserRepositoryInterface extends ObjectRepository
{
    public function prototype(): NPCQuestUser;

    public function save(NPCQuestUser $questUser): void;

    public function delete(NPCQuestUser $questUser): void;

    /**
     * @return array<NPCQuestUser>
     */
    public function getByQuest(int $questId): array;

    /**
     * @return array<NPCQuestUser>
     */
    public function getByUser(int $userId): array;

    public function getByQuestAndUser(int $questId, int $userId): ?NPCQuestUser;

    /**
     * @return array<NPCQuestUser>
     */
    public function getByQuestAndMode(int $questId, int $mode): array;

    /**
     * @return array<NPCQuestUser>
     */
    public function getUnrewardedByQuest(int $questId): array;

    public function truncateByQuest(int $questId): void;

    public function truncateByUser(int $userId): void;
}
