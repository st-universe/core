<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\NPCQuestLog;

/**
 * @extends ObjectRepository<NPCQuestLog>
 *
 * @method null|NPCQuestLog find(integer $id)
 */
interface NPCQuestLogRepositoryInterface extends ObjectRepository
{
    public function prototype(): NPCQuestLog;

    public function save(NPCQuestLog $log): void;

    public function delete(NPCQuestLog $log): void;

    /**
     * @return array<NPCQuestLog>
     */
    public function getByQuest(int $questId): array;

    /**
     * @return array<NPCQuestLog>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<NPCQuestLog>
     */
    public function getByQuestAndUser(int $questId, int $userId): array;

    /**
     * @return array<NPCQuestLog>
     */
    public function getActiveByQuest(int $questId): array;

    /**
     * @return array<NPCQuestLog>
     */
    public function getActiveByQuestAndUser(int $questId, int $userId): array;

    public function truncateByQuest(int $questId): void;

    public function truncateByUser(int $userId): void;
}
