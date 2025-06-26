<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\TradeLicenseInfo;

/**
 * @extends EntityRepository<TradeLicenseInfo>
 */
final class TradeLicenseInfoRepository extends EntityRepository implements TradeLicenseInfoRepositoryInterface
{
    #[Override]
    public function prototype(): TradeLicenseInfo
    {
        return new TradeLicenseInfo();
    }

    #[Override]
    public function save(TradeLicenseInfo $setLicense): void
    {
        $em = $this->getEntityManager();

        $em->persist($setLicense);
    }

    #[Override]
    public function delete(TradeLicenseInfo $setLicense): void
    {
        $em = $this->getEntityManager();

        $em->remove($setLicense);
        $em->flush();
    }

    #[Override]
    public function getLatestLicenseInfo(int $tradepostId): ?TradeLicenseInfo
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tli FROM %s tli WHERE tli.posts_id = :trade_post ORDER BY tli.id DESC',
                    TradeLicenseInfo::class
                )
            )
            ->setMaxResults(1)
            ->setParameters([
                'trade_post' => $tradepostId
            ])
            ->getOneOrNullResult();
    }
}
