<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyTerraforming;

/**
 * @extends ObjectRepository<ColonyTerraforming>
 *
 * @method null|ColonyTerraforming find(integer $id)
 */
interface ColonyTerraformingRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyTerraforming;

    public function save(ColonyTerraforming $terraforming): void;

    public function delete(ColonyTerraforming $terraforming): void;

    /**
     * @param array<Colony> $colonyies
     *
     * @return ColonyTerraforming[]
     */
    public function getByColony(array $colonyies): array;

    public function getByColonyAndField(int $colonyId, int $fieldId): ?ColonyTerraforming;

    /**
     * @return array<ColonyTerraforming>
     */
    public function getFinishedJobs(): array;
}
