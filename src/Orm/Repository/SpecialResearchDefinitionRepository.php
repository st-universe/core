<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Entity\SpecialResearchDefinition;

final class SpecialResearchDefinitionRepository implements SpecialResearchDefinitionRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[\Override]
    public function getByFaction(int $factionId): array
    {
        return $this->entityManager->createQuery(
            'SELECT d, c, commodity, data FROM ' . SpecialResearchDefinition::class . ' d
             LEFT JOIN d.costs c LEFT JOIN c.commodity commodity JOIN d.dataCommodity data
             WHERE d.faction IS NULL OR IDENTITY(d.faction) = :faction ORDER BY d.id ASC'
        )->setParameter('faction', $factionId)->getResult();
    }

    #[\Override]
    public function delete(SpecialResearchDefinition $definition): void
    {
        foreach ($definition->getCosts()->toArray() as $cost) {
            $definition->getCosts()->removeElement($cost);
            $this->entityManager->remove($cost);
        }
        $this->entityManager->remove($definition);
    }
}
