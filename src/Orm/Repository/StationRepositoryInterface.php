<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\TSpacecraftItemInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Station>
 *
 * @method null|Station find(integer $id)
 * @method Station[] findAll()
 */
interface StationRepositoryInterface extends ObjectRepository
{
    public function save(Station $station): void;

    public function delete(Station $station): void;

    /** @return array<Station> */
    public function getByUser(int $userId): array;

    /** @return array<Station> */
    public function getForeignStationsInBroadcastRange(Spacecraft $spacecraft): array;

    /** @return array<Station> */
    public function getTradePostsWithoutDatabaseEntry(): array;

    /** @return array<Station> */
    public function getByUplink(int $userId): array;

    /** @return array<Station> */
    public function getStationConstructions(): array;

    /**
     * @return array<TSpacecraftItemInterface>
     */
    public function getStationScannerResults(
        Spacecraft $spacecraft,
        bool $showCloaked = false,
        Map|StarSystemMap|null $field = null
    ): array;

    public function getStationOnLocation(Location $location): ?Station;

    /** @return array<Station> */
    public function getStationsByUser(int $userId): array;

    /** @return array<Station> */
    public function getByUserAndRump(User $user, SpacecraftRump $rump): array;

    /** @return array<Station> */
    public function getPiratePhalanxTargets(SpacecraftWrapperInterface $wrapper): array;
}
