<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradeLicenseInterface;

/**
 * @extends ObjectRepository<TradeLicense>
 *
 * @method null|TradeLicenseInterface find(integer $id)
 */
interface TradeLicenseRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeLicenseInterface;

    public function save(TradeLicenseInterface $post): void;

    public function delete(TradeLicenseInterface $post): void;

    public function truncateByUser(int $userId): void;

    public function truncateByUserAndTradepost(int $userId, int $tradePostId): void;

    /**
     * @return list<TradeLicenseInterface>
     */
    public function getByTradePost(int $tradePostId): array;

    /**
     * @return list<TradeLicenseInterface>
     */
    public function getByUser(int $userId): array;

    /**
     * @return list<TradeLicenseInterface>
     */
    public function getByTradePostAndNotExpired(int $tradePostId): array;

    public function getAmountByUser(int $userId): int;

    public function hasFergLicense(int $userId): bool;

    public function hasLicenseByUserAndTradePost(int $userId, int $tradePostId): bool;

    public function getLatestActiveLicenseByUserAndTradePost(int $userId, int $tradePostId): ?TradeLicenseInterface;

    public function getAmountByTradePost(int $tradePostId): int;

    public function hasLicenseByUserAndNetwork(int $userId, int $tradeNetworkId): bool;

    /**
     * @return list<TradeLicenseInterface>
     */
    public function getLicensesCountbyUser(int $userId): array;

    /**
     * @return array<TradeLicenseInterface>
     */
    public function getLicensesExpiredInLessThan(int $days): array;

    /**
     * @return array<int, TradeLicenseInterface>
     */
    public function getExpiredLicenses(): array;
}
