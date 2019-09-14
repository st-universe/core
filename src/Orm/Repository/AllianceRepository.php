<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceInterface;

final class AllianceRepository extends EntityRepository implements AllianceRepositoryInterface
{
    public function prototype(): AllianceInterface
    {
        return new Alliance();
    }

    public function save(AllianceInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush($post);
    }

    public function delete(AllianceInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush($post);
    }

    public function findAllOrdered(): array
    {
        return $this->findBy([], ['id' => 'asc']);
    }
}