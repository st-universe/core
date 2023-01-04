<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TerraformingInterface;

/**
 * @method null|TerraformingInterface find(integer $id)
 */
interface TerraformingRepositoryInterface extends ObjectRepository
{
    /**
     * @return TerraformingInterface[]
     */
    public function getBySourceFieldTypeAndUser(int $sourceFieldTypeId, int $userId): iterable;
}
