<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\NPCQuest;

/**
 * @extends ObjectRepository<NPCQuest>
 *
 * @method null|NPCQuest find(integer $id)
 */
interface NPCQuestRepositoryInterface extends ObjectRepository
{
    public function prototype(): NPCQuest;

    public function save(NPCQuest $quest): void;

    public function delete(NPCQuest $quest): void;

    /**
     * @return array<NPCQuest>
     */
    public function getActiveQuests(): array;

    /**
     * @return array<NPCQuest>
     */
    public function getOpenForApplications(): array;

    /**
     * @return array<NPCQuest>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<NPCQuest>
     */
    public function getByPlot(int $plotId): array;

    /**
     * @return array<NPCQuest>
     */
    public function getFinishedQuests(): array;

    /**
     * @return array<NPCQuest>
     */
    public function getActiveQuestsByUser(int $userId): array;

    /**
     * @return array<NPCQuest>
     */
    public function getFinishedQuestsByUser(int $userId): array;

    public function truncateByUser(int $userId): void;
}
