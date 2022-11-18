<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipCrewInterface;


/**
 * @method null|ShipCrewInterface find(integer $id)
 */
interface ShipCrewRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipCrewInterface;

    public function save(ShipCrewInterface $post): void;

    public function delete(ShipCrewInterface $post): void;

    /**
     * @return ShipCrewInterface[]
     */
    public function getByShip(int $shipId): array;

    /**
     * @return ShipCrewInterface[]
     */
    public function getByShipAndSlot(int $shipId, int $slotId): array;

    public function getByUserAtTradeposts(int $userId): array;

    public function getAmountByShip(int $shipId): int;

    public function getAmountByUserOnShips(int $userId): int;

    public function getCrewsTop10(): array;

    public function truncateByShip(int $shipId): void;
}
