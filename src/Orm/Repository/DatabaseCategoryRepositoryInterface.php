<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseCategoryInterface;

/**
 * @method null|DatabaseCategoryInterface find(integer $id)
 */
interface DatabaseCategoryRepositoryInterface extends ObjectRepository
{
    public function getByTypeId(int $type_id): array;
}