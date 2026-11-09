<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Orm\Entity\TechnologyResearch;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class ResearchHistory
{
    public function __construct(private SpecialResearchRepositoryInterface $foundations, private TechnologyRepositoryInterface $technology) {}

    /** @return list<array{finishedAt: int, research: string, subject: string, bonus: ?string}> */
    public function getRows(User $user): array
    {
        $rows = [];
        foreach ($this->foundations->getByUser($user) as $research) {
            if ($research->getStatus() === SpecialResearchStatusEnum::COMPLETED) {
                $rows[] = ['finishedAt' => $research->getFinishTime(), 'research' => 'Grundlagenforschung', 'subject' => $research->getResearchType()->getDescription(), 'bonus' => null];
            }
        }
        foreach ($this->technology->getResearchByUser($user) as $job) {
            if ($job->getStatus() === SpecialResearchStatusEnum::COMPLETED && $job->getKind()->isResearch()) {
                $rows[] = $this->technologyRow($job);
            }
        }
        usort($rows, static fn (array $a, array $b): int => [$a['finishedAt'], $a['research'], $a['subject']] <=> [$b['finishedAt'], $b['research'], $b['subject']]);
        return $rows;
    }

    /** @return array{finishedAt: int, research: string, subject: string, bonus: ?string} */
    private function technologyRow(TechnologyResearch $job): array
    {
        $optimization = $job->getKind() === TechnologyRecipeKindEnum::MODULE_OPTIMIZATION;
        return [
            'finishedAt' => $job->getFinishTime(),
            'research' => $optimization ? 'Moduloptimierung' : 'Nanitenfertigung',
            'subject' => $optimization ? ($job->getModule()?->getName() ?? $job->getResearchType()?->getDescription() ?? 'Unbekanntes Modul') : 'Borg-Naniten',
            'bonus' => $optimization ? '+' . number_format(min($job->getStartImprovementSteps() + 1, $job->getMaxImprovementSteps()) / 10, 1, ',', '') . '%' : null
        ];
    }
}
