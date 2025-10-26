<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\DatabaseCategoryAward;

/**
 * @extends EntityRepository<DatabaseCategoryAward>
 */
final class DatabaseCategoryAwardRepository extends EntityRepository implements DatabaseCategoryAwardRepositoryInterface
{

    #[\Override]
    public function findByCategoryIdAndLayerId(int $categoryId, ?int $layerId): ?DatabaseCategoryAward
    {
        return $this->findOneBy([
            'category_id' => $categoryId,
            'layer_id' => $layerId
        ]);
    }
}
