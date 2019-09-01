<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class TerraformingRepository extends EntityRepository implements TerraformingRepositoryInterface
{
    public function getBySourceFieldType(int $sourceFieldTypeId): array {
        return $this->findBy([
            'v_feld' => $sourceFieldTypeId
        ]);
    }
}