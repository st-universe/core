<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Spacecraft\Lib\TSpacecraftItemInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
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
    public function findFresh(int $spacecraftId): ?Spacecraft;

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

    /** @param array<int, int> $excludedIds */
    public function getRandomSpacecraftWithCrewByUser(int $userId, array $excludedIds = []): ?Spacecraft;

    public function getTractoringSpacecraft(Ship $tractoredShip): ?Spacecraft;

    /**
     * @return array<Spacecraft>
     */
    public function getAllTractoringSpacecrafts(): array;

    public function truncateAllSpacecrafts(): void;

    /**
     * @return Collection<int, Spacecraft>
     */
    public function getNearbySpacecraftsForWarpcoreTransfer(Spacecraft $spacecraft): Collection;

    /**
     * @return array<array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     user_id: int,
     *     user_name: string,
     *     alliance_id: null|int,
     *     alliance_name: null|string,
     *     rump_id: int,
     *     rump_name: string,
     *     x: int,
     *     y: int,
     *     in_system: bool,
     *     system_name: null|string,
     *     is_cloaked: bool
     * }>
     */
    public function getAdminLiveMapSpacecrafts(int $layerId): array;

    /**
     * @return array<array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     user_id: int,
     *     user_name: string,
     *     alliance_id: null|int,
     *     alliance_name: null|string,
     *     rump_id: int,
     *     rump_name: string,
     *     x: int,
     *     y: int,
     *     in_system: bool,
     *     system_name: null|string,
     *     is_cloaked: bool,
     *     hull: int,
     *     max_hull: int,
     *     shield: int,
     *     max_shield: int,
     *     eps: int,
     *     max_eps: int,
     *     warpdrive: int,
     *     max_warpdrive: int,
     *     alert_state: int
     * }>
     */
    public function getUserStarmapSpacecrafts(
        int $userId,
        int $layerId,
        ?int $allianceId,
        bool $includeAlliance,
        bool $includeFullLayer
    ): array;

    /**
     * @return array<array{
     *     source_id: int,
     *     x: int,
     *     y: int,
     *     sensor_range: int,
     *     tachyon_range: int
     * }>
     */
    public function getUserStarmapRealtimeSensorRanges(int $userId, int $layerId): array;

    /**
     * @return array<array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     user_id: int,
     *     user_name: string,
     *     alliance_id: null|int,
     *     alliance_name: null|string,
     *     rump_id: int,
     *     rump_name: string,
     *     x: int,
     *     y: int,
     *     in_system: bool,
     *     system_name: null|string,
     *     is_cloaked: bool,
     *     hull: int,
     *     max_hull: int,
     *     shield: int,
     *     max_shield: int,
     *     eps: int,
     *     max_eps: int,
     *     warpdrive: int,
     *     max_warpdrive: int,
     *     alert_state: int
     * }>
     */
    public function getUserStarmapRealtimeSpacecrafts(int $userId, int $layerId): array;
}
