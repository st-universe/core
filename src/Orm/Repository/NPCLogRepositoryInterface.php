<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\NPCLog;

/**
 * @extends ObjectRepository<NPCLog>
 *
 * @method null|NPCLog find(integer $id)
 * @method NPCLog[] findAll()
 */
interface NPCLogRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<NPCLog>
     */
    public function getRecent(): array;

    /**
     * @return array<NPCLog>
     */
    public function getByFactionAndSearch(?int $factionId, int $limit, string $search, int $sourceUserId): array;

    public function getAmountByFaction(?int $factionId): int;

    public function prototype(): NPCLog;

    public function save(NPCLog $npclog): void;

    public function delete(NPCLog $npclog): void;
}
