<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\AwardInterface;

/**
 * @extends EntityRepository<Award>
 */
final class AwardRepository extends EntityRepository implements AwardRepositoryInterface
{
    #[Override]
    public function save(AwardInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    #[Override]
    public function delete(AwardInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->remove($award);
        $em->flush(); //TODO really neccessary?
    }

    #[Override]
    public function prototype(): AwardInterface
    {
        return new Award();
    }
}
