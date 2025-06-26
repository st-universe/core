<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeLicenseInfo;

/**
 * @extends ObjectRepository<TradeLicenseInfo>
 *
 * @method null|TradeLicenseInfo find(integer $id)
 */
interface TradeLicenseInfoRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeLicenseInfo;

    public function save(TradeLicenseInfo $setLicense): void;

    public function delete(TradeLicenseInfo $setLicense): void;

    public function getLatestLicenseInfo(int $tradepostId): ?TradeLicenseInfo;
}
