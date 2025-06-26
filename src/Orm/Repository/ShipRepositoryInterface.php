<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Ship\Lib\TFleetShipItemInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Ship>
 *
 * @method null|Ship find(integer $id)
 * @method Ship[] findAll()
 */
interface ShipRepositoryInterface extends ObjectRepository
{
    public function save(Ship $post): void;

    public function delete(Ship $post): void;

    /**
     * @return array<Ship>
     */
    public function getByUserAndFleet(int $userId, ?int $fleetId): array;

    /**
     * @return array<Ship>
     */
    public function getByLocationAndUser(
        Location $location,
        User $user
    ): array;

    /**
     * @return iterable<Ship>
     */
    public function getPossibleFleetMembers(Ship $fleetLeader): iterable;

    /**
     * @return iterable<Ship>
     */
    public function getWithTradeLicensePayment(
        int $userId,
        int $tradePostShipId,
        int $commodityId,
        int $amount
    ): iterable;

    /**
     * @return array<Ship>
     */
    public function getEscapePods(): array;

    /**
     * @return array<Ship>
     */
    public function getEscapePodsByCrewOwner(User $user): array;

    /**
     * @return array<TFleetShipItemInterface>
     */
    public function getFleetShipsScannerResults(
        Spacecraft $spacecraft,
        bool $showCloaked = false,
        Map|StarSystemMap|null $field = null
    ): array;

    /**
     * @return array<Ship>
     */
    public function getAllDockedShips(): array;

    /**
     * @return array<Ship>
     */
    public function getPirateTargets(SpacecraftWrapperInterface $wrapper): array;

    /**
     * @return array<Ship>
     */
    public function getPirateFriends(SpacecraftWrapperInterface $wrapper): array;

    /**
     * @return array<Ship>
     */
    public function getByUserAndRump(User $user, SpacecraftRump $rump): array;
}
