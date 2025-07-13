<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\DatabaseEntry;

/**
 * @extends EntityRepository<ColonyClass>
 */
final class ColonyClassRepository extends EntityRepository implements ColonyClassRepositoryInterface
{
    #[Override]
    public function save(ColonyClass $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    #[Override]
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
                'categoryId' => DatabaseCategoryTypeEnum::COLONY_CLASS->value
            ])
            ->getResult();
    }
}
