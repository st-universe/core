<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TradeStorage;
use Stu\Orm\Entity\TradeStorageInterface;

final class TradeStorageRepository extends EntityRepository implements TradeStorageRepositoryInterface
{

    public function prototype(): TradeStorageInterface
    {
        return new TradeStorage();
    }

    public function save(TradeStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(TradeStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }
}
