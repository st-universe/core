<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseCategory;

/**
 * @extends ObjectRepository<DatabaseCategory>
 *
 * @method null|DatabaseCategory find(integer $id)
 * @method DatabaseCategory[] findAll()
 */
interface DatabaseCategoryRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<DatabaseCategory>
     */
    public function getByTypeId(int $type_id): array;
}
