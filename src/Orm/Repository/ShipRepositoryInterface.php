<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @method null|ShipInterface find(integer $id)
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
    public function getByInnerSystemLocation(
        int $starSystemId,
        int $sx,
        int $sy
    ): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getByOuterSystemLocation(
        int $cx,
        int $cy
    ): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getTradePostsWithoutDatabaseEntry(): iterable;

    /**
     * @return ShipInterface[]
     */
    public function getByUserAndFleetAndBase(int $userId, ?int $fleetId, bool $isBase): iterable;

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

    public function getSensorResultInnerSystem(ShipInterface $ship, StarSystemInterface $system = null): iterable;

    public function getSensorResultOuterSystem(int $cx, int $cy, int $sensorRange, bool $doSubspace, $ignoreId): iterable;

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
     * @return ShipInterface[]
     */
    public function getSingleShipScannerResults(
        ShipInterface $ship,
        bool $isBase,
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
}
