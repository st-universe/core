<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemInterface;

/**
 * @extends ObjectRepository<StarSystem>
 *
 * @method null|StarSystemInterface find(integer $id)
 */
interface StarSystemRepositoryInterface extends ObjectRepository
{
    public function prototype(): StarSystemInterface;

    public function save(StarSystemInterface $storage): void;
    /**
     * @return array<StarSystemInterface>
     */
    public function getByLayer(int $layerId): array;

    /**
     * @return array<StarSystemInterface>
     */
    public function getWithoutDatabaseEntry(): array;

    public function getNumberOfSystemsToGenerate(LayerInterface $layer): int;

    public function getRandomFreeSystemName(): string;
}
