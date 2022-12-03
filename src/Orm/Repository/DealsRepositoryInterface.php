<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DealsInterface;

/**
 * @method null|DealsInterface find(integer $id)
 */
interface DealsRepositoryInterface extends ObjectRepository
{
    public function prototype(): DealsInterface;

    public function save(DealsInterface $post): void;

    public function delete(DealsInterface $post): void;

    public function getDeals(int $userId): array;

    public function getFergLicense(int $userId): bool;

    public function getActiveDeals(int $userId): array;

    public function getActiveDealsGoods(int $userId): ?array;

    public function getActiveDealsShips(int $userId): array;

    public function getActiveDealsBuildplans(int $userId): array;

    public function getActiveDealsGoodsPrestige(int $userId): array;

    public function getActiveDealsShipsPrestige(int $userId): array;

    public function getActiveDealsBuildplansPrestige(int $userId): array;

    public function getActiveAuctions(int $userId): array;
}