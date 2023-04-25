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
     * @return iterable<ShipInterface>
     */
    public function getByUser(UserInterface $user): iterable;

    /**
     * @return array<ShipInterface>
     */
    public function getByUserAndRump(int $userId, int $rumpId): array;

    /**
     * @return iterable<ShipInterface>
     */
    public function getPossibleFleetMembers(ShipInterface $fleetLeader): iterable;

    /**
     * @return iterable<ShipInterface>
     */
    public function getShipsForAlertRed(
        ShipInterface $ship
    ): iterable;

    /**
     * @return array<ShipInterface>
     */
    public function getByLocationAndUser(
        ?StarSystemMapInterface $starSystemMap,
        ?MapInterface $map,
        UserInterface $user
    ): array;

    /**
     * @return array<ShipInterface>
     */
    public function getByLocation(
        ?StarSystemMapInterface $starSystemMap,
        ?MapInterface $map
    ): array;

    /**
     * @return array<ShipInterface>
     */
    public function getForeignStationsInBroadcastRange(ShipInterface $ship): array;

    /**
     * @return iterable<ShipInterface>
     */
    public function getTradePostsWithoutDatabaseEntry(): iterable;

    /**
     * @return array<ShipInterface>
     */
    public function getByUserAndFleetAndType(int $userId, ?int $fleetId, int $type): array;

    /**
     * @return array<ShipInterface>
     */
    public function getByUplink(int $userId): array;

    /**
     * @return iterable<ShipInterface>
     */
    public function getWithTradeLicensePayment(
        int $userId,
        int $tradePostShipId,
        int $commodityId,
        int $amount
    ): iterable;

    /**
     * @return iterable<ShipInterface>
     */
    public function getSuitableForShildRegeneration(int $regenerationThreshold): iterable;

    /**
     * @return iterable<ShipInterface>
     */
    public function getDebrisFields(): iterable;

    /**
     * @return iterable<ShipInterface>
     */
    public function getStationConstructions(): iterable;

    /**
     * @return iterable<ShipInterface>
     */
    public function getEscapePods(): iterable;

    /**
     * @return iterable<ShipInterface>
     */
    public function getEscapePodsByCrewOwner(int $userId): iterable;

    /**
     * @return iterable<ShipInterface>
     */
    public function getPlayerShipsForTick(): iterable;

    /**
     * @return iterable<ShipInterface>
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
    public function getSensorResultOuterSystem(int $cx, int $cy, int $layerId, int $sensorRange, bool $doSubspace, int $ignoreId): iterable;

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
    public function getSignaturesOuterSystemOfUser(int $minx, int $maxx, int $miny, int $maxy, int $layerId, int $userId): iterable;

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
    public function getSignaturesOuterSystemOfAlly(int $minx, int $maxx, int $miny, int $maxy, int $layerId, int $allyId): iterable;

    /**
     * @return iterable<array{
     *  fleetid: int,
     *  fleetname: string,
     *  isdefending: bool,
     *  isblocking: bool,
     *  shipid: int,
     *  rumpid: int,
     *  formerrumpid: int,
     *  warpstate: int,
     *  cloakstate: int,
     *  shieldstate: int,
     *  uplinkstate: int,
     *  isdestroyed: bool,
     *  spacecrafttype: int,
     *  shipname: string,
     *  hull: int,
     *  maxhull: int,
     *  shield: int,
     *  webid: int,
     *  webfinishtime: int,
     *  userid: int,
     *  username: string,
     *  rumpcategoryid: int,
     *  rumpname: string,
     *  rumproleid: int,
     *  haslogbook: bool
     * }>
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
     * @return array<ShipInterface>
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
     * @return array<ShipInterface>
     */
    public function getStationsByUser(int $userId): array;

    /**
     * @return array<ShipInterface>
     */
    public function getAllDockedShips(): array;

    /**
     * @return array<ShipInterface>
     */
    public function getAllTractoringShips(): array;

    public function truncateAllShips(): void;
}
