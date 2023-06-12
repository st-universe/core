<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\Researched;

/**
 * @extends EntityRepository<Building>
 */
final class BuildingRepository extends EntityRepository implements BuildingRepositoryInterface
{
    public function getByColonyAndUserAndBuildMenu(
        int $colonyId,
        int $userId,
        int $buildMenu,
        int $offset
    ): iterable {
        if ($userId === UserEnum::USER_NOONE) {

            return $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT b FROM %s b WHERE b.bm_col = :buildMenu
                    AND b.view = :viewState
                    AND b.id IN (
                            SELECT fb.buildings_id FROM %s fb WHERE fb.type IN (
                                SELECT fd.type_id FROM %s fd WHERE fd.colonies_id = :colonyId
                            )
                        ) ORDER BY b.name',
                        Building::class,
                        PlanetFieldTypeBuilding::class,
                        PlanetField::class
                    )
                )
                ->setMaxResults(ColonyEnum::BUILDMENU_SCROLLOFFSET)
                ->setFirstResult($offset)
                ->setParameters([
                    'viewState' => 1,
                    'buildMenu' => $buildMenu,
                    'colonyId' => $colonyId
                ])
                ->getResult();
        }

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT b FROM %s b WHERE b.bm_col = :buildMenu AND b.view = :viewState AND (
                        b.research_id is null OR b.research_id IN (
                            SELECT ru.research_id FROM %s ru WHERE ru.user_id = :userId AND ru.aktiv = :activeState
                        ) AND b.id IN (
                            SELECT fb.buildings_id FROM %s fb WHERE fb.type IN (
                                SELECT fd.type_id FROM %s fd WHERE fd.colonies_id = :colonyId
                            )
                        )) ORDER BY b.name',
                    Building::class,
                    Researched::class,
                    PlanetFieldTypeBuilding::class,
                    PlanetField::class
                )
            )
            ->setMaxResults(ColonyEnum::BUILDMENU_SCROLLOFFSET)
            ->setFirstResult($offset)
            ->setParameters([
                'activeState' => 0,
                'viewState' => 1,
                'buildMenu' => $buildMenu,
                'userId' => $userId,
                'colonyId' => $colonyId
            ])
            ->getResult();
    }
}
