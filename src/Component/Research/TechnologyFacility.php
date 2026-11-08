<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\TechnologyProduction;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\TechnologyResearch;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class TechnologyFacility
{
    public function __construct(private SpecialResearchFacility $researchFacility, private PlanetFieldRepositoryInterface $fields) {}

    public function canStart(Colony $colony, PlanetField $field, TechnologyRecipe $recipe): bool
    {
        if (!$this->researchFacility->isAvailable($colony, $field)) {
            return false;
        }
        return $recipe->getKind()->isResearch() || $this->hasFunction($field, BuildingFunctionEnum::TECH_CENTER);
    }

    public function getField(TechnologyResearch|TechnologyProduction $job): ?PlanetField
    {
        $colony = $job->getColony();
        if ($colony === null || $colony->getUser()->getId() !== $job->getUser()->getId()) {
            return null;
        }
        $field = $this->fields->getByColonyAndFieldId($colony->getId(), $job->getFieldId());
        if ($field === null || $field->isUnderConstruction() || $field->getIntegrity() <= 0 || $field->getBuildingId() !== $job->getBuildingId()) {
            return null;
        }
        $function = $job instanceof TechnologyResearch ? BuildingFunctionEnum::SPECIAL_RESEARCH : BuildingFunctionEnum::TECH_CENTER;
        return $this->hasFunction($field, $function) ? $field : null;
    }

    private function hasFunction(PlanetField $field, BuildingFunctionEnum $function): bool
    {
        return $field->getBuilding()?->getFunctions()->containsKey($function->value) ?? false;
    }
}
