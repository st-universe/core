<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use RuntimeException;
use Stu\Component\Research\ResearchHistory;
use Stu\Component\Research\SpecialResearchCatalog;
use Stu\Component\Research\SpecialResearchFacility;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Component\Research\TechnologyOverview;
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
        private SpecialResearchFacility $facility,
        private TechnologyOverview $technology,
        private ResearchHistory $history
    ) {}

    #[Override]
    public function setTemplateVariables($entity, TemplateInterface $template, User $user): void
    {
        if (!$entity instanceof Colony) {
            return;
        }
        $researches = $this->repository->getByUser($user);
        $running = array_find($researches, static fn (SpecialResearch $r): bool => $r->getStatus() === SpecialResearchStatusEnum::RUNNING);
        $field = $this->facility->find($entity, request::indInt('fid'));
        $active = $this->facility->isAvailable($entity, $field);
        $researchAvailable = $this->isIdle($running, $user);
        $template->setTemplateVar('SPECIAL_RESEARCH_FIELD', $field);
        $this->technology->setTemplateVariables($entity, $field, $template, $user);
        $rows = [];
        foreach ($this->catalog->getDefinitions($user->getFactionId()) as $definition) {
            $type = $definition->getResearchType();
            $research = array_find($researches, static fn (SpecialResearch $r): bool => $r->getResearchType() === $type);
            if ($research?->getStatus() === SpecialResearchStatusEnum::COMPLETED) {
                continue;
            }
            $hasChip = ($entity->getStorage()->get($definition->getDataCommodity()->getId())?->getAmount() ?? 0) > 0;
            if (!$hasChip && $research === null) {
                continue;
            }
            $costs = $this->getCosts($entity, $definition);
            $rows[] = [
                'type' => $type,
                'research' => $research,
                'costs' => $costs,
                'duration' => $research?->getStatus() === SpecialResearchStatusEnum::RUNNING
                    ? $research->getFinishTime() - $research->getStartTime() : $definition->getDuration(),
                'failureChance' => $research?->getStatus() === SpecialResearchStatusEnum::RUNNING
                    ? $research->getFailureChance() : $definition->getFailureChance(),
                'canStart' => $active && $researchAvailable
                    && array_all($costs, static fn (array $cost): bool => $cost['stored'] >= $cost['needed'])
            ];
        }
        $template->setTemplateVar('SPECIAL_RESEARCH_ROWS', $rows);
        $template->setTemplateVar('SPECIAL_RESEARCH_HISTORY', $this->history->getRows($user));
        $template->setTemplateVar('SPECIAL_RESEARCH_RUNNING', $running);
        $template->setTemplateVar('SPECIAL_RESEARCH_ACTIVE', $active);
    }

    private function isIdle(?SpecialResearch $running, User $user): bool
    {
        return $running === null && !$this->technology->hasRunningResearch($user);
    }

    /** @return list<array{commodity: Commodity, needed: int, stored: int}> */
    private function getCosts(Colony $colony, SpecialResearchDefinition $definition): array
    {
        $costs = [];
        foreach ($this->catalog->getCosts($definition) as $id => $needed) {
            $costs[] = [
                'commodity' => $this->commodities->find($id) ?? throw new RuntimeException('Research commodity missing'),
                'needed' => $needed,
                'stored' => $colony->getStorage()->get($id)?->getAmount() ?? 0
            ];
        }
        return $costs;
    }
}
