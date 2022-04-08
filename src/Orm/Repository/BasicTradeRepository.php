<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\BasicTradeInterface;

final class BasicTradeRepository extends EntityRepository implements BasicTradeRepositoryInterface
{
    public function prototype(): BasicTradeInterface
    {
        return new BasicTrade();
    }

    public function save(BasicTradeInterface $basicTrade): void
    {
        $em = $this->getEntityManager();

        $em->persist($basicTrade);
    }
}
