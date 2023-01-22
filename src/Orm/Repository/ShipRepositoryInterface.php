<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Ship>
 *
 * @method null|ShipInterface find(integer $id)
 * @method ShipInterface[] findAll()
 */
interface ShipRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipInterface;

    public function save(ShipInterface $post): void;

    public function delete(ShipInterface $post): void;

    public function getAmountByUserAndSpecialAbility(
        int $userId,
        int $specialAbilityId
    ): int;

    public function getAmountByUserAndRump(int $userId, int $shipRumpId): int;

    /**
     * @return ShipInterface[]
     */
    public function getByUser(UserInterface $user): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getByUserAndRump(int $userId, int $rumpId): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getPossibleFleetMembers(ShipInterface $fleetLeader): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getShipsForAlertRed(
        ShipInterface $ship
    ): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getByLocationAndUser(
        ?StarSystemMapInterface $starSystemMap,
        ?MapInterface $map,
        UserInterface $user
    ): array;

    /**
     * @return ShipInterface[]
     */
    public function getByLocation(
        ?StarSystemMapInterface $starSystemMap,
        ?MapInterface $map
    ): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getForeignStationsInBroadcastRange(ShipInterface $ship): array;

    /**
     * @return ShipInterface[]
     */
    public function getTradePostsWithoutDatabaseEntry(): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getByUserAndFleetAndType(int $userId, ?int $fleetId, int $type): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getByUplink(int $userId): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getWithTradeLicensePayment(
        int $userId,
        int $tradePostShipId,
        int $commodityId,
        int $amount
    ): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getSuitableForShildRegeneration(int $regenerationThreshold): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getDebrisFields(): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getStationConstructions(): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getEscapePods(): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getEscapePodsByCrewOwner(int $userId): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getPlayerShipsForTick(): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getNpcShipsForTick(): iterable;

    /**
     * @return iterable<array{
     *     posx: int,
     *     posy: int,
     *     sysid: int,
     *     shipcount: int,
     *     cloakcount: int,
     *     shieldstate: bool,
     *     type: int,
     *     d1c?: int,
     *     d2c?: int,
     *     d3c?: int,
     *     d4c?: int
     * }>
     */
    public function getSensorResultInnerSystem(
        ShipInterface $ship,
        int $ignoreId,
        StarSystemInterface $system = null
    ): iterable;

    /**
     * @return iterable<array{
     *     posx: int,
     *     posy: int,
     *     sysid: int,
     *     shipcount: int,
     *     cloakcount: int,
     *     allycolor: string,
     *     usercolor: string,
     *     factioncolor: string,
     *     type: int,
     *     d1c?: int,
     *     d2c?: int,
     *     d3c?: int,
     *     d4c?: int
     * }>
     */
    public function getSensorResultOuterSystem(int $cx, int $cy, int $sensorRange, bool $doSubspace, int $ignoreId): iterable;

    /**
     * @return iterable<array{
     *     posx: int,
     *     posy: int,
     *     shipcount: int,
     *     type: int,
     *     d1c: int,
     *     d2c: int,
     *     d3c: int,
     *     d4c: int
     * }>
     */
    public function getSignaturesOuterSystemOfUser(int $minx, int $maxx, int $miny, int $maxy, int $userId): iterable;

    /**
     * @return iterable<array{
     *     posx: int,
     *     posy: int,
     *     shipcount: int,
     *     type: int,
     *     d1c: int,
     *     d2c: int,
     *     d3c: int,
     *     d4c: int
     * }>
     */
    public function getSignaturesOuterSystemOfAlly(int $minx, int $maxx, int $miny, int $maxy, int $allyId): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getFleetShipsScannerResults(
        ShipInterface $ship,
        bool $showCloaked = false,
        int $mapId = null,
        int $sysMapId = null
    ): iterable;

    /**
     * @param array<int> $types
     *
     * @return ShipInterface[]
     */
    public function getSingleShipScannerResults(
        ShipInterface $ship,
        array $types,
        bool $showCloaked = false,
        int $mapId = null,
        int $sysMapId = null
    ): iterable;

    public function isCloakedShipAtShipLocation(
        ShipInterface $ship
    ): bool;

    public function isCloakedShipAtLocation(
        ?int $sysMapId,
        ?int $mapId,
        int $ignoreId
    ): bool;

    public function getRandomShipIdWithCrewByUser(int $userId): ?int;

    public function isBaseOnLocation(ShipInterface $ship): bool;

    /**
     * @return ShipInterface[]
     */
    public function getStationsByUser(int $userId): array;
}
