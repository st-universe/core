<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BuildplanHangarInterface;

final class BuildplanHangarRepository extends EntityRepository implements BuildplanHangarRepositoryInterface
{
    public function getByRump(int $rumpId): ?BuildplanHangarInterface {
        return $this->findOneBy([
            'rump_id' => $rumpId
        ]);
    }
}