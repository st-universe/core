<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Trumfield;

/**
 * @extends EntityRepository<Trumfield>
 */
final class TrumfieldRepository extends EntityRepository implements TrumfieldRepositoryInterface
{
    #[\Override]
    public function prototype(): Trumfield
    {
        return new Trumfield();
    }

    #[\Override]
    public function save(Trumfield $trumfield): void
    {
        $em = $this->getEntityManager();

        $em->persist($trumfield);
    }

    #[\Override]
    public function delete(Trumfield $trumfield): void
    {
        $em = $this->getEntityManager();

        $em->remove($trumfield);
    }
}
