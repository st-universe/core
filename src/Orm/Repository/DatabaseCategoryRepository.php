<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\DatabaseCategory;

/**
 * @extends EntityRepository<DatabaseCategory>
 */
final class DatabaseCategoryRepository extends EntityRepository implements DatabaseCategoryRepositoryInterface
{
    public function getByTypeId(int $type_id): array
    {
        return $this->findBy([
            'type' => $type_id
        ]);
    }
}
