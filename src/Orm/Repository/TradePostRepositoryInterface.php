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

    public function save(TradePostInterface $tradePost): void;

    public function delete(TradePostInterface $tradePost): void;

    /**
     * @return TradePostInterface[]
     */
    public function getByUserLicense(int $userId): array;

    public function getByUserLicenseOnlyNPC(int $userId): array;

    public function getByUserLicenseOnlyFerg(int $userId): array;

    public function getTradePostIdByShip(int $ship_id): int;

    public function getClosestNpcTradePost(int $cx, int $cy): TradePostInterface;
}