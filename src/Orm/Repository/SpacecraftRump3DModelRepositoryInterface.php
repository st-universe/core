<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRump3DModel;

/**
 * @extends ObjectRepository<SpacecraftRump3DModel>
 *
 * @method SpacecraftRump3DModel[] findAll()
 */
interface SpacecraftRump3DModelRepositoryInterface extends ObjectRepository
{
    public function save(SpacecraftRump3DModel $entity): void;

    public function getBySpacecraftRump(SpacecraftRump $rump): ?SpacecraftRump3DModel;
}
