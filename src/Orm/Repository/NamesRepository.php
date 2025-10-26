<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\NameTypeEnum;
use Stu\Orm\Entity\Names;
use Stu\Orm\Entity\StarSystem;

/**
 * @extends EntityRepository<Names>
 */
final class NamesRepository extends EntityRepository implements NamesRepositoryInterface
{
    #[\Override]
    public function save(Names $name): void
    {
        $em = $this->getEntityManager();

        $em->persist($name);
    }

    #[\Override]
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

    #[\Override]
    public function getRandomFreeSystemNames(int $amount): array
    {
        $freeNames = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT n FROM %s n
                    WHERE n.type = :type
                    AND NOT EXISTS (SELECT ss.id
                                        FROM %s ss
                                        WHERE ss.name = n.name)',
                    Names::class,
                    StarSystem::class
                )
            )
            ->setParameter('type', NameTypeEnum::STAR_SYSTEM->value)
            ->getResult();

        shuffle($freeNames);

        return array_slice($freeNames, 0, $amount);
    }
}
