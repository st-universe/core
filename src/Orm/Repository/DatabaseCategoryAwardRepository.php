<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\DatabaseCategoryAward;
use Stu\Orm\Entity\DatabaseCategoryAwardInterface;

/**
 * @extends EntityRepository<DatabaseCategoryAward>
 */
final class DatabaseCategoryAwardRepository extends EntityRepository implements DatabaseCategoryAwardRepositoryInterface
{

    #[Override]
    public function findByCategoryIdAndLayerId(int $categoryId, ?int $layerId): ?DatabaseCategoryAwardInterface
    {
        return $this->findOneBy([
            'category_id' => $categoryId,
            'layer_id' => $layerId
        ]);
    }
}
