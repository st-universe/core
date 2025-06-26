<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeLicense;

/**
 * @extends ObjectRepository<TradeLicense>
 *
 * @method null|TradeLicense find(integer $id)
 */
interface TradeLicenseRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeLicense;

    public function save(TradeLicense $post): void;

    public function delete(TradeLicense $post): void;

    public function truncateByUser(int $userId): void;

    public function truncateByUserAndTradepost(int $userId, int $tradePostId): void;

    /**
     * @return array<TradeLicense>
     */
    public function getByTradePost(int $tradePostId): array;

    /**
     * @return array<TradeLicense>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<TradeLicense>
     */
    public function getByTradePostAndNotExpired(int $tradePostId): array;

    public function getAmountByUser(int $userId): int;

    public function hasFergLicense(int $userId): bool;

    public function hasLicenseByUserAndTradePost(int $userId, int $tradePostId): bool;

    public function getLatestActiveLicenseByUserAndTradePost(int $userId, int $tradePostId): ?TradeLicense;

    public function getAmountByTradePost(int $tradePostId): int;

    public function hasLicenseByUserAndNetwork(int $userId, int $tradeNetworkId): bool;

    /**
     * @return array<TradeLicense>
     */
    public function getLicensesCountbyUser(int $userId): array;

    /**
     * @return array<TradeLicense>
     */
    public function getLicensesExpiredInLessThan(int $days): array;

    /**
     * @return array<int, TradeLicense>
     */
    public function getExpiredLicenses(): array;
}
