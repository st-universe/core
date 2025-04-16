<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface TerraformingInterface
{
    public function getId(): int;

    public function getDescription(): string;

    public function setDescription(string $description): TerraformingInterface;

    public function getEnergyCosts(): int;

    public function setEnergyCosts(int $energyCosts): TerraformingInterface;

    public function getFromFieldTypeId(): int;

    public function setFromFieldTypeId(int $fromFieldTypeId): TerraformingInterface;

    public function getToFieldTypeId(): int;

    public function setToFieldTypeId(int $toFieldTypeId): TerraformingInterface;

    public function getDuration(): int;

    public function setDuration(int $duration): TerraformingInterface;

    public function getResearchId(): ?int;

    public function setResearchId(?int $researchId): TerraformingInterface;

    /**
     * @return Collection<int, TerraformingCostInterface>
     */
    public function getCosts(): Collection;

    /**
     * @return Collection<int, ColonyClassRestrictionInterface>
     */
    public function getRestrictions(): Collection;
}