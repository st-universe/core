<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use LogicException;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\TechnologyProduction;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\TechnologyResearch;

final class TechnologyJobFactory
{
    public function __construct(private StuTime $time, private SpecialResearchCatalog $catalog) {}

    public function research(Colony $colony, PlanetField $field, TechnologyRecipe $recipe, ?Module $module, AwayMissionResearchEnum $system, int $chance, int $steps): TechnologyResearch
    {
        $job = new TechnologyResearch();
        $this->bind($job, $colony, $field, $recipe);
        $job->setKind($recipe->getKind())->setModule($module)->setResearchType($system)
            ->setSuccessChance($chance)->setStartImprovementSteps($steps)
            ->setMaxImprovementSteps($recipe->getSettings()['max_steps'] ?? 0)
            ->setAccidentChance(($recipe->getSettings()['accident_chance'] ?? 0) * 100)
            ->setFailureSettings($this->catalog->getDefinition($system, $colony->getUser()->getFactionId())?->getFailureSettings() ?? []);
        return $job;
    }

    public function production(Colony $colony, PlanetField $field, TechnologyRecipe $recipe): TechnologyProduction
    {
        $job = new TechnologyProduction();
        $this->bind($job, $colony, $field, $recipe);
        $job->setKind($recipe->getKind())
            ->setOutputAmount($recipe->getOutputAmount())
            ->setOutputCommodity($recipe->getOutputCommodity() ?? throw new LogicException('Production commodity missing'));
        return $job;
    }

    private function bind(TechnologyResearch|TechnologyProduction $job, Colony $colony, PlanetField $field, TechnologyRecipe $recipe): void
    {
        $now = $this->time->time();
        $job->setUser($colony->getUser())->setColony($colony)->setRecipe($recipe)
            ->setFieldId($field->getId())->setBuildingId($field->getBuildingId() ?? throw new LogicException('Technology building missing'))
            ->setStartTime($now)->setFinishTime($now + $recipe->getDuration());
    }
}
