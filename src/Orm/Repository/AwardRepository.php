<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Award;

/**
 * @extends EntityRepository<Award>
 */
final class AwardRepository extends EntityRepository implements AwardRepositoryInterface
{
    #[\Override]
    public function save(Award $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    #[\Override]
    public function delete(Award $award): void
    {
        $em = $this->getEntityManager();

        $em->remove($award);
        $em->flush(); //TODO really neccessary?
    }

    #[\Override]
    public function prototype(): Award
    {
        return new Award();
    }
}
