<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TradeLicenseInfoInterface;
use Stu\Orm\Entity\TradeLicenseInfo;

final class TradeLicenseInfoRepository extends EntityRepository implements TradeLicenseInfoRepositoryInterface
{

    public function prototype(): TradeLicenseInfoInterface
    {
        return new TradeLicenseInfo();
    }

    public function save(TradeLicenseInfoInterface $setLicense): void
    {
        $em = $this->getEntityManager();

        $em->persist($setLicense);
    }

    public function delete(TradeLicenseInfoInterface $setLicense): void
    {
        $em = $this->getEntityManager();

        $em->remove($setLicense);
        $em->flush();
    }

    public function getLatestLicenseInfo(int $tradepostId): ?TradeLicenseInfoInterface
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
