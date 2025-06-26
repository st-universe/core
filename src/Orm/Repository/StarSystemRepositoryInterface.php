<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\StarSystem;

/**
 * @extends ObjectRepository<StarSystem>
 *
 * @method null|StarSystem find(integer $id)
 */
interface StarSystemRepositoryInterface extends ObjectRepository
{
    public function prototype(): StarSystem;

    public function save(StarSystem $storage): void;

    /**
     * @return array<StarSystem>
     */
    public function getByLayer(int $layerId): array;

    /**
     * @return array<StarSystem>
     */
    public function getWithoutDatabaseEntry(): array;

    public function getNumberOfSystemsToGenerate(Layer $layer): int;

    public function getPreviousStarSystem(StarSystem $current): ?StarSystem;

    public function getNextStarSystem(StarSystem $current): ?StarSystem;

    /**
     * @return array<StarSystem>
     */
    public function getPirateHides(SpacecraftWrapperInterface $wrapper): array;
}
