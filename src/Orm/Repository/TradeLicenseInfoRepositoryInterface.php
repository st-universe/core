<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeLicenseInfoInterface;

/**
 * @method null|TradeLicenseInfoInterface find(integer $id)
 */ #
interface TradeLicenseInfoRepositoryInterface extends ObjectRepository
{

    public function prototype(): TradeLicenseInfoInterface;

    public function save(TradeLicenseInfoInterface $setLicense): void;

    public function delete(TradeLicenseInfoInterface $setLicense): void;

    public function getLatestLicenseInfo(int $tradepostId): ?TradeLicenseInfoInterface;
}
