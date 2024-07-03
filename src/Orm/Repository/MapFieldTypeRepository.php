<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\MapFieldTypeInterface;

/**
 * @extends EntityRepository<MapFieldType>
 */
final class MapFieldTypeRepository extends EntityRepository implements MapFieldTypeRepositoryInterface
{
    #[Override]
    public function save(MapFieldTypeInterface $map): void
    {
        $em = $this->getEntityManager();

        $em->persist($map);
    }
}
