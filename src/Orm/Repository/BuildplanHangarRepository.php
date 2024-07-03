<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\BuildplanHangar;
use Stu\Orm\Entity\BuildplanHangarInterface;

/**
 * @extends EntityRepository<BuildplanHangar>
 */
final class BuildplanHangarRepository extends EntityRepository implements BuildplanHangarRepositoryInterface
{
    #[Override]
    public function getByRump(int $rumpId): ?BuildplanHangarInterface
    {
        return $this->findOneBy([
            'rump_id' => $rumpId
        ]);
    }
}
