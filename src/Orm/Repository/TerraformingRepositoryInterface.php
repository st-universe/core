<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Terraforming;
use Stu\Orm\Entity\TerraformingInterface;

/**
 * @extends ObjectRepository<Terraforming>
 *
 * @method null|TerraformingInterface find(integer $id)
 */
interface TerraformingRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<TerraformingInterface>
     */
    public function getBySourceFieldTypeAndUser(int $sourceFieldTypeId, int $userId, int $colonyClassId): array;
}