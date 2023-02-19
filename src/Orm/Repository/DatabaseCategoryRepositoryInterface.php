<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseCategoryInterface;

/**
 * @extends ObjectRepository<DatabaseCategory>
 *
 * @method null|DatabaseCategoryInterface find(integer $id)
 * @method DatabaseCategoryInterface[] findAll()
 */
interface DatabaseCategoryRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<DatabaseCategoryInterface>
     */
    public function getByTypeId(int $type_id): array;
}
