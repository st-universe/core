<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradePostInterface;

/**
 * @method null|TradePostInterface find(integer $id)
 */
interface TradePostRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradePostInterface;

    public function save(TradePostInterface $setTradePost): void;

    public function delete(TradePostInterface $setTradePost): void;

    /**
     * @return TradePostInterface[]
     */
    public function getByUserLicense(int $userId): array;

    public function getByUserLicenseOnlyNPC(int $userId): array;

    public function getTradePostIdByShip(int $ship_id): int;
}