<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Module\Ship\Lib\TFleetShipItemInterface;
use Stu\Module\Ship\Lib\TShipItemInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipInterface;
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
        MapInterface|StarSystemMapInterface $field,
        UserInterface $user
    ): array;

    /**
     * @return array<ShipInterface>
     */
    public function getByLocation(MapInterface|StarSystemMapInterface $field): array;

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
    public function getByUserAndFleetAndType(int $userId, ?int $fleetId, SpacecraftTypeEnum $type): array;

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
     * @return array<TFleetShipItemInterface>
     */
    public function getFleetShipsScannerResults(
        ShipInterface $ship,
        bool $showCloaked = false,
        MapInterface|StarSystemMapInterface $field = null
    ): array;

    /**
     * @param array<int> $types
     *
     * @return array<TShipItemInterface>
     */
    public function getSingleShipScannerResults(
        ShipInterface $ship,
        array $types,
        bool $showCloaked = false,
        MapInterface|StarSystemMapInterface $field = null
    ): array;

    public function isCloakedShipAtShipLocation(
        ShipInterface $ship
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

    /**
     * @return array<ShipInterface>
     */
    public function getPirateTargets(ShipInterface $ship): array;

    /**
     * @return array<ShipInterface>
     */
    public function getPirateFriends(ShipInterface $ship): array;

    public function truncateAllShips(): void;
}
