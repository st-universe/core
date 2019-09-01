<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\TerraformingInterface;

interface TerraformingRepositoryInterface extends ObjectRepository
{
    /**
     * @return TerraformingInterface[]
     */
    public function getBySourceFieldType(int $sourceFieldTypeId): array;
}