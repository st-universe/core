<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftRump;

interface BorgAssimilationRepositoryInterface
{
    public function isAssimilateable(SpacecraftRump $rump): bool;
    public function getBySpacecraft(Spacecraft $spacecraft): ?SpacecraftAssimilated;
    public function getBySpacecraftId(int $id): ?SpacecraftAssimilated;
    /** @return list<SpacecraftAssimilated> */
    public function getActive(): array;
    /** @return list<SpacecraftAssimilated> */
    public function getBySource(Spacecraft $source): array;
    public function save(SpacecraftAssimilated $assimilation): void;
    public function delete(SpacecraftAssimilated $assimilation): void;
    public function lock(Spacecraft $spacecraft): void;
    public function deleteByRump(SpacecraftRump $rump): void;
}
