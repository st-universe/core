<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleSpecial;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

/**
 * @extends EntityRepository<Module>
 */
final class ModuleRepository extends EntityRepository implements ModuleRepositoryInterface
{
    // used for ModuleSelector
    #[Override]
    public function getBySpecialTypeAndRumpAndRole(
        ColonyInterface|ShipInterface $host,
        SpacecraftModuleTypeEnum $moduleType,
        int $rumpId,
        int $shipRumpRoleId
    ): array {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                        m.id, m.name, m.level, m.upgrade_factor, m.default_factor, m.downgrade_factor, m.crew,
                        m.type, m.research_id, m.commodity_id, m.viewable, m.rumps_role_id, m.ecost, m.faction_id,
                        m.system_type
                    FROM stu_modules m
                    WHERE m.type = :typeId
                    AND (SELECT CASE WHEN (SELECT count(id)
                                            FROM stu_modules
                                            WHERE type = :typeId
                                            AND rumps_role_id = :shipRumpRoleId) = 0
                                    THEN m.rumps_role_id IS NULL
                                    ELSE m.rumps_role_id = :shipRumpRoleId
                                END)
					AND (m.viewable = :state OR m.commodity_id IN (SELECT commodity_id
                                                                FROM stu_storage
                                                                WHERE :hostIdColumnName = :hostId))
                    AND m.id IN (SELECT module_id
                                FROM stu_modules_specials
                                WHERE special_id IN (SELECT module_special_id
                                                    FROM stu_rumps_module_special
                                                    WHERE rump_id = :rumpId))
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleType->value,
                'hostIdColumnName' => $host instanceof ColonyInterface ? 'colony_id' : 'ship_id',
                'hostId' => $host->getId(),
                'shipRumpRoleId' => $shipRumpRoleId,
                'rumpId' => $rumpId,
                'state' => 1
            ])
            ->getResult();
    }

    #[Override]
    public function getBySpecialTypeAndRump(
        ColonyInterface|SpacecraftInterface $host,
        SpacecraftModuleTypeEnum $moduleType,
        int $rumpId
    ): array {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                    m.id, m.name, m.level, m.upgrade_factor, m.default_factor, m.downgrade_factor, m.crew,
                    m.type, m.research_id, m.commodity_id, m.viewable, m.rumps_role_id, m.ecost, m.faction_id,
                    m.system_type
                FROM stu_modules m
                WHERE m.type = :typeId
                AND (m.viewable = :state OR m.commodity_id IN (SELECT commodity_id
                                                            FROM stu_storage
                                                            WHERE :hostIdColumnName = :hostId))
                AND m.id IN (SELECT module_id
                            FROM stu_modules_specials
                            WHERE special_id IN (SELECT module_special_id
                                                FROM stu_rumps_module_special
                                                WHERE rump_id = :rumpId))
            ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleType->value,
                'hostIdColumnName' => $host instanceof ColonyInterface ? 'colony_id' : 'ship_id',
                'hostId' => $host->getId(),
                'rumpId' => $rumpId,
                'state' => 1
            ])
            ->getResult();
    }


    // used for ModuleSelector
    #[Override]
    public function getByTypeColonyAndLevel(
        int $colonyId,
        SpacecraftModuleTypeEnum $moduleType,
        int $shipRumpRoleId,
        array $moduleLevel
    ): array {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                        m.id, m.name, m.level, m.upgrade_factor, m.default_factor, m.downgrade_factor, m.crew,
                        m.type, m.research_id, m.commodity_id, m.viewable, m.rumps_role_id, m.ecost, m.faction_id,
                        m.system_type
                    FROM stu_modules m
                    WHERE m.type = :typeId
                    AND (SELECT CASE WHEN (SELECT count(id)
                                            FROM stu_modules
                                            WHERE type = :typeId
                                            AND rumps_role_id = :shipRumpRoleId) = 0
                                    THEN m.rumps_role_id IS NULL
                                    ELSE m.rumps_role_id = :shipRumpRoleId
                                END)
					AND level IN (:levelList)
					AND (m.viewable = :state OR m.commodity_id IN (SELECT commodity_id
                                                                FROM stu_storage
                                                                WHERE colony_id = :colonyId))
                    ORDER BY m.level ASC, m.id ASC
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleType->value,
                'colonyId' => $colonyId,
                'shipRumpRoleId' => $shipRumpRoleId,
                'levelList' => $moduleLevel,
                'state' => 1
            ])
            ->getResult();
    }

    // used for admin createBuildplan
    #[Override]
    public function getByTypeAndLevel(
        int $moduleTypeId,
        int $shipRumpRoleId,
        array $moduleLevel
    ): array {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT m.id, m.name, m.level, m.upgrade_factor, m.default_factor, m.downgrade_factor,
                        m.crew, m.type, m.research_id, m.commodity_id, m.viewable, m.rumps_role_id,
                        m.ecost, m.faction_id, m.system_type
                    FROM stu_modules m
                    WHERE m.type = :typeId
                    AND (SELECT CASE WHEN (SELECT count(id)
                                        FROM stu_modules
                                        WHERE type = :typeId
                                        AND rumps_role_id = :shipRumpRoleId) = 0
                                    THEN m.rumps_role_id IS NULL
                                    ELSE m.rumps_role_id = :shipRumpRoleId
                                END)
					AND level IN (:levelList)
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleTypeId,
                'shipRumpRoleId' => $shipRumpRoleId,
                'levelList' => $moduleLevel
            ])
            ->getResult();
    }

    // used for admin createBuildplan
    #[Override]
    public function getBySpecialTypeIds(array $specialTypeIds): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m WHERE m.id IN (
                        SELECT ms.module_id FROM %s ms WHERE ms.special_id IN (:specialTypeIds)
                    )
                    AND m.type = 9',
                    Module::class,
                    ModuleSpecial::class
                )
            )
            ->setParameters([
                'specialTypeIds' => $specialTypeIds
            ])
            ->getResult();
    }

    private function getResultSetMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Module::class, 'm');
        $rsm->addFieldResult('m', 'id', 'id');
        $rsm->addFieldResult('m', 'name', 'name');
        $rsm->addFieldResult('m', 'level', 'level');
        $rsm->addFieldResult('m', 'upgrade_factor', 'upgrade_factor');
        $rsm->addFieldResult('m', 'default_factor', 'default_factor');
        $rsm->addFieldResult('m', 'downgrade_factor', 'downgrade_factor');
        $rsm->addFieldResult('m', 'crew', 'crew');
        $rsm->addFieldResult('m', 'type', 'type');
        $rsm->addFieldResult('m', 'research_id', 'research_id');
        $rsm->addFieldResult('m', 'commodity_id', 'commodity_id');
        $rsm->addFieldResult('m', 'viewable', 'viewable');
        $rsm->addFieldResult('m', 'rumps_role_id', 'rumps_role_id');
        $rsm->addFieldResult('m', 'ecost', 'ecost');
        $rsm->addFieldResult('m', 'faction_id', 'faction_id');
        $rsm->addFieldResult('m', 'system_type', 'system_type');

        return $rsm;
    }
}
