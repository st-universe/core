<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SpacecraftSystem;

/**
 * @extends ObjectRepository<SpacecraftSystem>
 */
interface SpacecraftSystemRepositoryInterface extends ObjectRepository
{
    public function prototype(): SpacecraftSystem;

    public function save(SpacecraftSystem $post): void;

    public function delete(SpacecraftSystem $post): void;

    /**
     * @return array<SpacecraftSystem>
     */
    public function getByShip(int $shipId): array;

    /**
     * @return array<SpacecraftSystem>
     */
    public function getTrackingShipSystems(int $targetId): array;

    /**
     * @return array<SpacecraftSystem>
     */
    public function getWebConstructingShipSystems(int $webId): array;

    public function getWebOwningShipSystem(int $webId): ?SpacecraftSystem;

    public function truncateByShip(int $shipId): void;
}
