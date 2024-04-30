<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\PirateWrathInterface;

/**
 * @extends EntityRepository<PirateWrath>
 * 
 * @method PirateWrathInterface[] findAll()
 */
final class PirateWrathRepository extends EntityRepository implements PirateWrathRepositoryInterface
{
    public function save(PirateWrathInterface $wrath): void
    {
        $em = $this->getEntityManager();

        $em->persist($wrath);
    }

    public function delete(PirateWrathInterface $wrath): void
    {
        $em = $this->getEntityManager();

        $em->remove($wrath);
    }

    public function prototype(): PirateWrathInterface
    {
        return new PirateWrath();
    }
}
