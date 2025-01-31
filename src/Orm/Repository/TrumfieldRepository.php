<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Entity\TrumfieldInterface;

/**
 * @extends EntityRepository<Trumfield>
 */
final class TrumfieldRepository extends EntityRepository implements TrumfieldRepositoryInterface
{
    #[Override]
    public function prototype(): TrumfieldInterface
    {
        return new Trumfield();
    }

    #[Override]
    public function save(TrumfieldInterface $trumfield): void
    {
        $em = $this->getEntityManager();

        $em->persist($trumfield);
    }

    #[Override]
    public function delete(TrumfieldInterface $trumfield): void
    {
        $em = $this->getEntityManager();

        $em->remove($trumfield);
    }
}
