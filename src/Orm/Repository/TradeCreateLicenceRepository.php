<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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

        $em->persist($post);
    }

    public function delete(TradeLicenceCreationInterface $setLicence): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getByTradePost(int $posts_id): array
    {
        return $this->findBy([
            'tradepost' => $posts_id
        ]);
    }
}