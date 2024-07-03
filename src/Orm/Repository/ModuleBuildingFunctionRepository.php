<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\ModuleBuildingFunction;

/**
 * @extends EntityRepository<ModuleBuildingFunction>
 */
final class ModuleBuildingFunctionRepository extends EntityRepository implements ModuleBuildingFunctionRepositoryInterface
{
    #[Override]
    public function getByBuildingFunctionAndUser(int $buildingFunction, int $userId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(ModuleBuildingFunction::class, 'mbf');
        $rsm->addFieldResult('mbf', 'id', 'id');
        $rsm->addFieldResult('mbf', 'buildingfunction', 'buildingfunction');
        $rsm->addFieldResult('mbf', 'module_id', 'module_id');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT mbf.id, mbf.module_id, mbf.buildingfunction FROM stu_modules_buildingfunction mbf
            WHERE mbf.buildingfunction = :buildingFunction AND mbf.module_id IN (
                SELECT m.id FROM stu_modules m WHERE m.research_id is null OR m.research_id IN (
                    SELECT r.research_id FROM stu_researched r WHERE r.aktiv = :activeState AND r.user_id = :userId
                )
            ) ORDER BY mbf.module_id',
            $rsm
        )
            ->setParameters([
                'buildingFunction' => $buildingFunction,
                'userId' => $userId,
                'activeState' => 0
            ])
            ->getResult();
    }
}
