<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TradeLicenceCreation;
use Stu\Orm\Entity\TradeLicenceCreationInterface;

final class TradeCreateLicenceRepository extends EntityRepository implements TradeCreateLicenceRepositoryInterface
{

    public function prototype(): TradeLicenceCreationInterface
    {
        return new TradeLicenceCreation();
    }

    public function save(TradeLicenceCreationInterface $setLicence): void
    {
        $em = $this->getEntityManager();

        $em->persist($setLicence);
    }

    public function delete(TradeLicenceCreationInterface $setLicence): void
    {
        $em = $this->getEntityManager();

        $em->remove($setLicence);
        $em->flush();
    }

    public function getLatestLicenseInfo(int $tradepostId): TradeLicenceCreationInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tlc FROM %s tlc WHERE tlc.posts_id = :trade_post ORDER BY tlc.id DESC',
                    TradeLicenceCreation::class
                )
            )
            ->setMaxResults(1)
            ->setParameters([
                'trade_post' => $tradepostId
            ])
            ->getSingleResult();
    }
}
