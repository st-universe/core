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


    public function getAmountByShip(int $shipId): int;

    public function getAmountByUser(int $userId): int;

    public function truncateByShip(int $shipId): void;
}
