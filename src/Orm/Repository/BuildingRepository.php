<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Colony\ColonyEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\ResearchInterface;

/**
 * @extends EntityRepository<Building>
 */
final class BuildingRepository extends EntityRepository implements BuildingRepositoryInterface
{
    public function getBuildmenuBuildings(
        PlanetFieldHostInterface $host,
        int $userId,
        int $buildMenu,
        int $offset,
        int $commodityId = null,
        int $fieldType = null
    ): array {

        $commodityFilter = $commodityId === null ? '' : sprintf(
            'AND EXISTS (SELECT bc.id FROM %s bc WHERE bc.buildings_id = b.id AND bc.commodity_id = %d)',
            BuildingCommodity::class,
            $commodityId
        );

        $fieldTypeFilter = $fieldType === null ? '' : sprintf(
            'AND EXISTS (SELECT pftb.id FROM %s pftb WHERE pftb.buildings_id = b.id AND pftb.type = %d)',
            PlanetFieldTypeBuilding::class,
            $fieldType
        );

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT b FROM %s b
                    WHERE b.bm_col = :buildMenu
                    AND b.view = :viewState
                    AND (b.research_id is null OR b.research_id IN (
                            SELECT ru.research_id FROM %s ru WHERE ru.user_id = :userId AND ru.aktiv = :activeState
                        ) AND b.id IN (
                            SELECT fb.buildings_id FROM %s fb WHERE fb.type IN (
                                SELECT fd.type_id FROM %s fd WHERE fd.%s = :hostId
                            )
                        ))
                    %s %s
                    ORDER BY b.name',
                    Building::class,
                    Researched::class,
                    PlanetFieldTypeBuilding::class,
                    PlanetField::class,
                    $host->getPlanetFieldHostColumnIdentifier(),
                    $commodityFilter,
                    $fieldTypeFilter
                )
            )
            ->setMaxResults(ColonyEnum::BUILDMENU_SCROLLOFFSET)
            ->setFirstResult($offset)
            ->setParameters([
                'activeState' => 0,
                'viewState' => 1,
                'buildMenu' => $buildMenu,
                'userId' => $userId,
                'hostId' => $host->getId()
            ])
            ->getResult();
    }

    public function getByResearch(ResearchInterface $research): array
    {
        return $this->findBy([
            'research_id' => $research->getId()
        ]);
    }
}
