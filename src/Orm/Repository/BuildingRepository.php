<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Colony\ColonyEnum;
use Stu\Orm\Entity\Building;

final class BuildingRepository extends EntityRepository implements BuildingRepositoryInterface
{
    public function getByColonyAndUserAndBuildMenu(
        int $colonyId,
        int $userId,
        int $buildMenu,
        int $offset
    ): iterable {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Building::class, 'b');
        $rsm->addFieldResult('b', 'id', 'id');
        $rsm->addFieldResult('b', 'name', 'name');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT b.* FROM stu_buildings b WHERE b.bm_col = :buildMenu AND b.view = :viewState AND (
                    b.research_id is null OR b.research_id IN (
                        SELECT ru.research_id FROM stu_researched ru WHERE ru.user_id = :userId AND aktiv = :activeState
                    ) AND id IN (
                        SELECT fb.buildings_id FROM stu_field_build fb WHERE fb.type IN (
                            SELECT fd.type FROM stu_colonies_fielddata fd WHERE colonies_id = :colonyId
                        )
                    )
                ) ORDER BY b.name LIMIT :offset,:scrollOffset',
                $rsm
            )
            ->setParameters([
                'activeState' => 0,
                'viewState' => 1,
                'buildMenu' => $buildMenu,
                'userId' => $userId,
                'colonyId' => $colonyId,
                'offset' => $offset,
                'scrollOffset' => ColonyEnum::BUILDMENU_SCROLLOFFSET,
            ])
            ->getResult();
    }
}
