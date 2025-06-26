<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseCategoryAward;

/**
 * @extends ObjectRepository<DatabaseCategoryAward>
 *
 * @method null|DatabaseCategoryAward find(integer $id)
 * @method DatabaseCategoryAward[] findAll()
 */
interface DatabaseCategoryAwardRepositoryInterface extends ObjectRepository
{
    public function findByCategoryIdAndLayerId(int $categoryId, ?int $layerId): ?DatabaseCategoryAward;
}
