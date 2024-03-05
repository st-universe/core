<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Names;
use Stu\Component\Game\NameTypeEnum;
use Stu\Orm\Entity\NamesInterface;

/**
 * @extends EntityRepository<Names>
 */
final class NamesRepository extends EntityRepository implements NamesRepositoryInterface
{

    public function save(NamesInterface $name): void
    {
        $em = $this->getEntityManager();

        $em->persist($name);
    }

    public function mostUnusedNames(): array
    {
        $query = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT pn
                 FROM %1$s pn
                 WHERE pn.count = (
                     SELECT MIN(pn2.count)
                     FROM %1$s pn2
                     WHERE pn2.type = :type
                 ) 
                 AND pn.type = :type',
                Names::class
            )
        )->setParameters([
            'type' => NameTypeEnum::KAZON_SHIP->value
        ]);

        return $query->getResult();
    }
}
