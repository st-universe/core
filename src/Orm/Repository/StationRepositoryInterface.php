<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\TSpacecraftItemInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Station>
 *
 * @method null|StationInterface find(integer $id)
 * @method StationInterface[] findAll()
 */
interface StationRepositoryInterface extends ObjectRepository
{
    public function save(StationInterface $station): void;

    public function delete(StationInterface $station): void;

    /** @return array<StationInterface> */
    public function getByUser(int $userId): array;

    /** @return array<StationInterface> */
    public function getForeignStationsInBroadcastRange(SpacecraftInterface $spacecraft): array;

    /** @return array<StationInterface> */
    public function getTradePostsWithoutDatabaseEntry(): array;

    /** @return array<StationInterface> */
    public function getByUplink(int $userId): array;

    /** @return array<StationInterface> */
    public function getStationConstructions(): array;

    /**
     * @return array<TSpacecraftItemInterface>
     */
    public function getStationScannerResults(
        SpacecraftInterface $spacecraft,
        bool $showCloaked = false,
        MapInterface|StarSystemMapInterface|null $field = null
    ): array;

    public function getStationOnLocation(LocationInterface $location): ?StationInterface;

    /** @return array<StationInterface> */
    public function getStationsByUser(int $userId): array;

    /** @return array<StationInterface> */
    public function getByUserAndRump(UserInterface $user, SpacecraftRumpInterface $rump): array;

    /** @return array<StationInterface> */
    public function getPiratePhalanxTargets(SpacecraftWrapperInterface $wrapper): array;
}
