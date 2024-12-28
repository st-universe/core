<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\SpacecraftSystemInterface;

/**
 * @extends ObjectRepository<SpacecraftSystem>
 */
interface SpacecraftSystemRepositoryInterface extends ObjectRepository
{
    public function prototype(): SpacecraftSystemInterface;

    public function save(SpacecraftSystemInterface $post): void;

    public function delete(SpacecraftSystemInterface $post): void;

    /**
     * @return array<SpacecraftSystemInterface>
     */
    public function getByShip(int $shipId): array;

    /**
     * @return array<SpacecraftSystemInterface>
     */
    public function getTrackingShipSystems(int $targetId): array;

    public function getByShipAndModule(int $shipId, int $moduleId): ?SpacecraftSystemInterface;

    /**
     * @return array<SpacecraftSystemInterface>
     */
    public function getWebConstructingShipSystems(int $webId): array;

    public function getWebOwningShipSystem(int $webId): ?SpacecraftSystemInterface;

    public function truncateByShip(int $shipId): void;
}
