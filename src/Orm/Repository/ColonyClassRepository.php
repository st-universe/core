<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\ColonyClass;

final class ColonyClassRepository extends EntityRepository implements ColonyClassRepositoryInterface
{
    public function getWithoutDatabaseEntry(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT p FROM %s p WHERE p.database_id NOT IN (SELECT d.id FROM %s d WHERE d.category_id = :categoryId)',
                    ColonyClass::class,
                    DatabaseEntry::class
                )
            )
            ->setParameters([
                'categoryId' => DatabaseCategoryTypeEnum::DATABASE_CATEGORY_COLONY_CLASS
            ])
            ->getResult();
    }
}
