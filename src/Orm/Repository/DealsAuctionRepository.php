<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\DealsAuction;
use Stu\Orm\Entity\DealsAuctionInterface;

final class DealsAuctionRepository extends EntityRepository implements DealsAuctionRepositoryInterface
{

    public function prototype(): DealsAuctionInterface
    {
        return new DealsAuction();
    }

    public function save(DealsAuctionInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(DealsAuctionInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }
}