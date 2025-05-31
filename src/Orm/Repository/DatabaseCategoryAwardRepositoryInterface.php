<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseCategoryAward;
use Stu\Orm\Entity\DatabaseCategoryAwardInterface;

/**
 * @extends ObjectRepository<DatabaseCategoryAward>
 *
 * @method null|DatabaseCategoryAwardInterface find(integer $id)
 * @method DatabaseCategoryAwardInterface[] findAll()
 */
interface DatabaseCategoryAwardRepositoryInterface extends ObjectRepository
{
    public function findByCategoryIdAndLayerId(int $categoryId, ?int $layerId): ?DatabaseCategoryAwardInterface;
}
