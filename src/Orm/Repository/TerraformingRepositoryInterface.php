<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\Terraforming;

/**
 * @extends ObjectRepository<Terraforming>
 *
 * @method null|Terraforming find(integer $id)
 */
interface TerraformingRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<Terraforming>
     */
    public function getBySourceFieldTypeAndUser(int $sourceFieldTypeId, int $userId, ColonyClass $colonyClass): array;
}
