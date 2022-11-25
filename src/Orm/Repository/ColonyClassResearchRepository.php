<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyClassInterface;

final class ColonyClassResearchRepository extends EntityRepository implements ColonyClassResearchRepositoryInterface
{
    public function getByColonyClass(ColonyClassInterface $colonyClass): array
    {
        return $this->findBy([
            'planet_type_id' => $colonyClass
        ]);
    }
}
