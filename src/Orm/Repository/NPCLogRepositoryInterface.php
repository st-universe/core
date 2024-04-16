<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\NPCLog;
use Stu\Orm\Entity\NPCLogInterface;

/**
 * @extends ObjectRepository<NPCLog>
 *
 * @method null|NPCLogInterface find(integer $id)
 * @method NPCLogInterface[] findAll()
 */
interface NPCLogRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<NPCLogInterface>
     */
    public function getRecent(): array;

    public function prototype(): NPCLogInterface;

    public function save(NPCLogInterface $npclog): void;

    public function delete(NPCLOgInterface $npclog): void;

    public function truncateAllEntities(): void;
}
