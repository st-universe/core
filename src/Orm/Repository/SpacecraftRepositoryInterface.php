<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Spacecraft\Lib\TSpacecraftItemInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Spacecraft>
 *
 * @method null|Spacecraft find(integer $id)
 * @method Spacecraft[] findAll()
 */
interface SpacecraftRepositoryInterface extends ObjectRepository
{
    public function save(Spacecraft $spacecraft): void;

    public function delete(Spacecraft $spacecraft): void;

    public function getAmountByUserAndSpecialAbility(
        int $userId,
        int $specialAbilityId
    ): int;

    public function getAmountByUserAndRump(int $userId, int $spacecraftRumpId): int;

    /**
     * @return array<Spacecraft>
     */
    public function getByUser(User $user): array;

    /**
     * @return array<Spacecraft>
     */
    public function getSuitableForShieldRegeneration(): array;

    /**
     * @return iterable<Spacecraft>
     */
    public function getPlayerSpacecraftsForTick(): iterable;

    /** @return array<Spacecraft> */
    public function getNpcSpacecraftsForTick(): array;

    public function isCloakedSpacecraftAtLocation(
        Spacecraft $spacecraft
    ): bool;

    /** @return array<TSpacecraftItemInterface> */
    public function getSingleSpacecraftScannerResults(
        Spacecraft $spacecraft,
        bool $showCloaked = false,
        Map|StarSystemMap|null $field = null
    ): array;

    public function getRandomSpacecraftWithCrewByUser(int $userId): ?Spacecraft;

    /**
     * @return array<Spacecraft>
     */
    public function getAllTractoringSpacecrafts(): array;

    public function truncateAllSpacecrafts(): void;

    /**
     * @return Collection<int, Spacecraft>
     */
    public function getNearbySpacecraftsForWarpcoreTransfer(Spacecraft $spacecraft): Collection;
}
