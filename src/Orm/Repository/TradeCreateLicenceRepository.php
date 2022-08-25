<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\TradeLicenceCreation;
use Stu\Orm\Entity\TradeLicenceCreationInterface;
use Stu\Orm\Entity\TradePost;

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

    public function getUserByTradepost(int $posts_id): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT tp.user_id FROM %s tp WHERE tp.id = :trade_post',
                    TradePost::class
                )
            )
            ->setParameters([
                'trade_post' => $posts_id
            ])
            ->getResult();
    }
}