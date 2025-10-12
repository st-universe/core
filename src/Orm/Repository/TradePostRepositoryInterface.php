<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<TradePost>
 *
 * @method null|TradePost find(integer $id)
 * @method TradePost[] findAll()
 *
 */
interface TradePostRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradePost;

    public function save(TradePost $tradePost): void;

    public function delete(TradePost $tradePost): void;

    /**
     * @return array<TradePost>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<TradePost>
     */
    public function getByUserLicense(int $userId): array;

    /**
     * @return array<TradePost>
     */
    public function getByUserLicenseOnlyNPC(int $userId): array;

    /**
     * @return array<TradePost>
     */
    public function getByUserLicenseOnlyFerg(int $userId): array;

    public function getClosestTradePost(Location $location, User $user): ?TradePost;

    /**
     * @return array<User>
     */
    public function getUsersWithStorageOnTradepost(int $tradePostId): array;

    public function truncateAllTradeposts(): void;
}
