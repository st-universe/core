<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Research\SpecialResearchCatalog;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\SpecialResearchDefinition;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;

final class SpecialResearchProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private SpecialResearchRepositoryInterface $repository,
        private SpecialResearchCatalog $catalog,
        private CommodityRepositoryInterface $commodities,
        private ColonyFunctionManagerInterface $functions
    ) {}

    #[\Override]
    public function setTemplateVariables($entity, TemplateInterface $template, User $user): void
    {
        if (!$entity instanceof Colony) {
            return;
        }
        $researches = $this->repository->getByUser($user);
        $running = array_find($researches, static fn (SpecialResearch $r): bool => $r->getStatus() === SpecialResearchStatusEnum::RUNNING);
        $active = $this->functions->hasActiveFunction($entity, BuildingFunctionEnum::SPECIAL_RESEARCH, false);
        $rows = [];
        foreach ($this->catalog->getDefinitions($user->getFactionId()) as $definition) {
            $type = $definition->getResearchType();
            $research = array_find($researches, static fn (SpecialResearch $r): bool => $r->getResearchType() === $type);
            $hasChip = ($entity->getStorage()->get($definition->getDataCommodity()->getId())?->getAmount() ?? 0) > 0;
            if (!$hasChip && $research === null) {
                continue;
            }
            $costs = $this->getCosts($entity, $definition);
            $completed = $research?->getStatus() === SpecialResearchStatusEnum::COMPLETED;
            $rows[] = [
                'type' => $type,
                'research' => $research,
                'costs' => $costs,
                'completed' => $completed,
                'duration' => $research?->getStatus() === SpecialResearchStatusEnum::RUNNING
                    ? $research->getFinishTime() - $research->getStartTime() : $definition->getDuration(),
                'failureChance' => $research?->getStatus() === SpecialResearchStatusEnum::RUNNING
                    ? $research->getFailureChance() : $definition->getFailureChance(),
                'canStart' => $active && $running === null && !$completed
                    && array_all($costs, static fn (array $cost): bool => $cost['stored'] >= $cost['needed'])
            ];
        }
        $template->setTemplateVar('SPECIAL_RESEARCH_ROWS', $rows);
        $template->setTemplateVar('SPECIAL_RESEARCH_RUNNING', $running);
        $template->setTemplateVar('SPECIAL_RESEARCH_ACTIVE', $active);
    }

    /** @return list<array{commodity: Commodity, needed: int, stored: int}> */
    private function getCosts(Colony $colony, SpecialResearchDefinition $definition): array
    {
        $costs = [];
        foreach ($this->catalog->getCosts($definition) as $id => $needed) {
            $costs[] = [
                'commodity' => $this->commodities->find($id) ?? throw new \RuntimeException('Research commodity missing'),
                'needed' => $needed,
                'stored' => $colony->getStorage()->get($id)?->getAmount() ?? 0
            ];
        }
        return $costs;
    }
}
