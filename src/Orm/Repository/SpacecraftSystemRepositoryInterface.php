<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\Spacecraft;
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

    public function isSystemHealthy(Spacecraft $spacecraft, SpacecraftSystemTypeEnum $type): bool;

    public function truncateByShip(int $shipId): void;
}
