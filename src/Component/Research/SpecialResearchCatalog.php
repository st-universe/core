<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Orm\Entity\SpecialResearchDefinition;
use Stu\Orm\Repository\SpecialResearchDefinitionRepositoryInterface;

final class SpecialResearchCatalog
{
    public function __construct(private SpecialResearchDefinitionRepositoryInterface $repository) {}

    /** @return array<string, SpecialResearchDefinition> */
    public function getDefinitions(int $factionId): array
    {
        $definitions = [];
        foreach ($this->repository->getByFaction($factionId) as $definition) {
            $key = $definition->getResearchType()->value;
            if (!isset($definitions[$key]) || $definition->getFaction() !== null) {
                $definitions[$key] = $definition;
            }
        }
        return array_filter($definitions, static fn (SpecialResearchDefinition $definition): bool => $definition->isValid());
    }

    public function getDefinition(AwayMissionResearchEnum $type, int $factionId): ?SpecialResearchDefinition
    {
        return $this->getDefinitions($factionId)[$type->value] ?? null;
    }

    /** @return array<int, int> */
    public function getCosts(SpecialResearchDefinition $definition): array
    {
        $costs = [$definition->getDataCommodity()->getId() => $definition->getDataAmount()];
        foreach ($definition->getCosts() as $cost) {
            $id = $cost->getCommodity()->getId();
            $costs[$id] = ($costs[$id] ?? 0) + $cost->getAmount();
        }
        return $costs;
    }
}
