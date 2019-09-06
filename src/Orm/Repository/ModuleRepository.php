<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\Module;

final class ModuleRepository extends EntityRepository implements ModuleRepositoryInterface
{
    public function getBySpecialTypeAndRump(
        int $colonyId,
        int $moduleTypeId,
        int $shipRumpId,
        int $shipRumpRoleId
    ): array {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                        m.id, m.name, m.level, m.upgrade_factor, m.downgrade_factor, m.crew, m.type, m.research_id, m.goods_id, m.viewable, m.rumps_role_id, m.ecost
                    FROM stu_modules m WHERE m.type = :typeId AND
					(SELECT CASE WHEN (SELECT count(id) FROM stu_modules where type = :typeId AND rumps_role_id = :shipRumpRoleId)=0 THEN m.rumps_role_id IS NULL ELSE m.rumps_role_id = :shipRumpRoleId END)
					AND (m.viewable = 1 OR m.goods_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id = :colonyId))
                    AND m.id IN (SELECT module_id FROM stu_modules_specials WHERE special_id IN (SELECT module_special_id FROM stu_rumps_module_special WHERE rump_id = :shipRumpId))
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleTypeId,
                'colonyId' => $colonyId,
                'shipRumpRoleId' => $shipRumpRoleId,
                'shipRumpId' => $shipRumpId
            ])
            ->getResult();
    }

    public function getByTypeAndLevel(
        int $colonyId,
        int $moduleTypeId,
        int $shipRumpRoleId,
        array $moduleLevel
    ): array {

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                        m.id, m.name, m.level, m.upgrade_factor, m.downgrade_factor, m.crew, m.type, m.research_id, m.goods_id, m.viewable, m.rumps_role_id, m.ecost
                    FROM stu_modules m WHERE m.type = :typeId AND (SELECT CASE WHEN (SELECT count(id) FROM stu_modules where type = :typeId AND rumps_role_id = :shipRumpRoleId) = 0 THEN m.rumps_role_id IS NULL ELSE m.rumps_role_id = :shipRumpRoleId END)
					AND level IN (:levelList)
					AND (m.viewable = 1 OR m.goods_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id = :colonyId))
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleTypeId,
                'colonyId' => $colonyId,
                'shipRumpRoleId' => $shipRumpRoleId,
                'levelList' => $moduleLevel
            ])
            ->getResult();
    }

    private function getResultSetMapping(): ResultSetMapping {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Module::class, 'm');
        $rsm->addFieldResult('m', 'id', 'id');
        $rsm->addFieldResult('m', 'name', 'name');
        $rsm->addFieldResult('m', 'level', 'level');
        $rsm->addFieldResult('m', 'upgrade_factor', 'upgrade_factor');
        $rsm->addFieldResult('m', 'downgrade_factor', 'downgrade_factor');
        $rsm->addFieldResult('m', 'crew', 'crew');
        $rsm->addFieldResult('m', 'type', 'type');
        $rsm->addFieldResult('m', 'research_id', 'research_id');
        $rsm->addFieldResult('m', 'goods_id', 'goods_id');
        $rsm->addFieldResult('m', 'viewable', 'viewable');
        $rsm->addFieldResult('m', 'rumps_role_id', 'rumps_role_id');
        $rsm->addFieldResult('m', 'ecost', 'ecost');

        return $rsm;
    }
}