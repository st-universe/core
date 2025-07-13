<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\StarSystemType;

/**
 * @extends EntityRepository<StarSystemType>
 */
final class StarSystemTypeRepository extends EntityRepository implements StarSystemTypeRepositoryInterface
{
    #[Override]
    public function save(StarSystemType $type): void
    {
        $em = $this->getEntityManager();

        $em->persist($type);
    }

    #[Override]
    public function getWithoutDatabaseEntry(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT t FROM %s t WHERE t.database_id NOT IN (SELECT d.id FROM %s d WHERE d.category_id = :categoryId)',
                    StarSystemType::class,
                    DatabaseEntry::class
                )
            )
            ->setParameters([
                'categoryId' => DatabaseCategoryTypeEnum::STAR_SYSTEM_TYPE->value,
            ])
            ->getResult();
    }
}
