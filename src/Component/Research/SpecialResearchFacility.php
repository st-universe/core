<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class SpecialResearchFacility
{
    public function __construct(private PlanetFieldRepositoryInterface $fields) {}

    public function find(Colony $colony, int $fieldId = 0): ?PlanetField
    {
        if ($fieldId !== 0) {
            $field = $this->fields->getByColonyAndFieldId($colony->getId(), $fieldId);
            return $field !== null && $this->isResearchBuilding($field) ? $field : null;
        }
        $fields = $this->fields->getByColonyAndBuildingFunction($colony->getId(), [BuildingFunctionEnum::SPECIAL_RESEARCH->value]);
        usort($fields, static fn (PlanetField $a, PlanetField $b): int => $a->getFieldId() <=> $b->getFieldId());
        return array_find($fields, fn (PlanetField $field): bool => $this->isAvailable($colony, $field))
            ?? array_find($fields, fn (PlanetField $field): bool => $this->isResearchBuilding($field));
    }

    public function isAvailable(Colony $colony, ?PlanetField $field): bool
    {
        return $field !== null && $field->getHost() === $colony && $field->isActive() && $this->isResearchBuilding($field);
    }

    public function getResearchField(SpecialResearch $research): ?PlanetField
    {
        $colony = $research->getColony();
        $fieldId = $research->getFieldId();
        if ($colony === null || $fieldId === null || $colony->getUser()->getId() !== $research->getUser()->getId()) {
            return null;
        }
        $field = $this->fields->getByColonyAndFieldId($colony->getId(), $fieldId);
        return $field !== null && $this->isResearchBuilding($field) && $field->getBuildingId() === $research->getBuildingId() ? $field : null;
    }

    public function isIntact(SpecialResearch $research): bool
    {
        $colony = $research->getColony();
        if ($colony === null || $colony->getUser()->getId() !== $research->getUser()->getId()) {
            return false;
        }
        return $research->getFieldId() === null || $this->getResearchField($research) !== null;
    }

    private function isResearchBuilding(PlanetField $field): bool
    {
        return !$field->isUnderConstruction() && $field->getIntegrity() > 0
            && ($field->getBuilding()?->getFunctions()->containsKey(BuildingFunctionEnum::SPECIAL_RESEARCH->value) ?? false);
    }
}
