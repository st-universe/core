<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Names;
use Stu\Orm\Entity\NamesInterface;
use Stu\Component\Game\GameEnum;

/**
 * @extends EntityRepository<Names>
 */
final class NamesRepository extends EntityRepository implements NamesRepositoryInterface
{
    public function prototype(): NamesInterface
    {
        return new Names();
    }

    public function save(NamesInterface $name): void
    {
        $em = $this->getEntityManager();

        $em->persist($name);
        $em->flush();
    }

    public function delete(NamesInterface $name): void
    {
        $em = $this->getEntityManager();

        $em->remove($name);
        $em->flush();
    }

    public function mostUnusedNames(): array
    {
        $query = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT pn
                 FROM %s pn
                 WHERE pn.count = (
                     SELECT MIN(pn2.count)
                     FROM %s pn2
                     WHERE pn2.type = :type
                 ) 
                 AND pn.type = :type',
                Names::class,
                Names::class
            )
        )->setParameters([
            'type' => GameEnum::KAZON_SHIP_NAME_TYPE
        ]);

        return $query->getResult();
    }
}
