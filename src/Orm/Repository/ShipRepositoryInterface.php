<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Ship\Lib\TFleetShipItemInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;
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
    public function save(ShipInterface $post): void;

    public function delete(ShipInterface $post): void;

    /**
     * @return array<ShipInterface>
     */
    public function getByUserAndFleet(int $userId, ?int $fleetId): array;

    /**
     * @return array<ShipInterface>
     */
    public function getByLocationAndUser(
        LocationInterface $location,
        UserInterface $user
    ): array;

    /**
     * @return iterable<ShipInterface>
     */
    public function getPossibleFleetMembers(ShipInterface $fleetLeader): iterable;

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
     * @return array<ShipInterface>
     */
    public function getEscapePods(): array;

    /**
     * @return array<ShipInterface>
     */
    public function getEscapePodsByCrewOwner(int $userId): array;

    /**
     * @return array<TFleetShipItemInterface>
     */
    public function getFleetShipsScannerResults(
        SpacecraftInterface $spacecraft,
        bool $showCloaked = false,
        MapInterface|StarSystemMapInterface|null $field = null
    ): array;

    /**
     * @return array<ShipInterface>
     */
    public function getAllDockedShips(): array;

    /**
     * @return array<ShipInterface>
     */
    public function getPirateTargets(SpacecraftWrapperInterface $wrapper): array;

    /**
     * @return array<ShipInterface>
     */
    public function getPirateFriends(SpacecraftWrapperInterface $wrapper): array;

    /**
     * @return array<ShipInterface>
     */
    public function getByUserAndRump(UserInterface $user, SpacecraftRumpInterface $rump): array;
}
