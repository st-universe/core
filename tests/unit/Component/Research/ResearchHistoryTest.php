<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\TechnologyResearch;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Stu\StuTestCase;

final class ResearchHistoryTest extends StuTestCase
{
    public function testCombinesCompletedResearchChronologicallyWithoutProductionOrFailedAttempts(): void
    {
        $user = new User();
        $foundations = $this->mock(SpecialResearchRepositoryInterface::class);
        $technology = $this->mock(TechnologyRepositoryInterface::class);
        $foundation = new SpecialResearch()->setResearchType(AwayMissionResearchEnum::SENSORS)->setStatus(SpecialResearchStatusEnum::COMPLETED)->setFinishTime(100);
        $foundations->shouldReceive('getByUser')->once()->with($user)->andReturn([
            new SpecialResearch()->setResearchType(AwayMissionResearchEnum::EPS)->setStatus(SpecialResearchStatusEnum::FAILED)->setFinishTime(10),
            $foundation,
            new SpecialResearch()->setResearchType(AwayMissionResearchEnum::SHIELDS)->setFinishTime(20)
        ]);
        $technology->shouldReceive('getResearchByUser')->once()->with($user)->andReturn([
            $this->job(200, TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setModule(new Module()->setName('Sensoren Klasse 2'))->setStartImprovementSteps(16)->setMaxImprovementSteps(70),
            $this->job(50, TechnologyRecipeKindEnum::NANITE_RESEARCH),
            $this->job(80, TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setModule(new Module()->setName('Schilde Klasse 1'))->setMaxImprovementSteps(70),
            $this->job(30, TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setStatus(SpecialResearchStatusEnum::FAILED),
            $this->job(40, TechnologyRecipeKindEnum::NANITE_RESEARCH)->setStatus(SpecialResearchStatusEnum::RUNNING),
            $this->job(60, TechnologyRecipeKindEnum::DATASET_COPY),
            $this->job(70, TechnologyRecipeKindEnum::DATASET_COPY)
        ]);
        $technology->shouldNotReceive('getProductionByUser');
        $rows = new ResearchHistory($foundations, $technology)->getRows($user);
        self::assertSame([50, 80, 100, 200], array_column($rows, 'finishedAt'));
        self::assertSame(['Nanitenfertigung', 'Moduloptimierung', 'Grundlagenforschung', 'Moduloptimierung'], array_column($rows, 'research'));
        self::assertSame(['Borg-Naniten', 'Schilde Klasse 1', 'Sensoren', 'Sensoren Klasse 2'], array_column($rows, 'subject'));
        self::assertSame([null, '+0,1%', null, '+1,7%'], array_column($rows, 'bonus'));
        self::assertSame(100, $foundation->getFinishTime());
    }

    public function testHistoricalOptimizationBonusIsCappedAtItsOwnSnapshotLimit(): void
    {
        $user = new User();
        $foundations = $this->mock(SpecialResearchRepositoryInterface::class);
        $technology = $this->mock(TechnologyRepositoryInterface::class);
        $foundations->shouldReceive('getByUser')->once()->with($user)->andReturn([]);
        $technology->shouldReceive('getResearchByUser')->once()->with($user)->andReturn([
            $this->job(50, TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setModule(new Module()->setName('Impulsantrieb'))->setStartImprovementSteps(69)->setMaxImprovementSteps(70),
            $this->job(60, TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setModule(new Module()->setName('Warpantrieb'))->setStartImprovementSteps(70)->setMaxImprovementSteps(70)
        ]);
        $rows = new ResearchHistory($foundations, $technology)->getRows($user);
        self::assertSame(['+7,0%', '+7,0%'], array_column($rows, 'bonus'));
    }

    private function job(int $finishedAt, TechnologyRecipeKindEnum $kind): TechnologyResearch
    {
        return new TechnologyResearch()->setFinishTime($finishedAt)->setKind($kind)->setStatus(SpecialResearchStatusEnum::COMPLETED);
    }
}
