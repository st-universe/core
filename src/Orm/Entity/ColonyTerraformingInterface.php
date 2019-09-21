<?php

namespace Stu\Orm\Entity;

interface ColonyTerraformingInterface
{
    public function getId(): int;

    public function getColonyId(): int;

    public function getFieldId(): int;

    public function getTerraformingId(): int;

    public function setTerraformingId(int $terraformingId): ColonyTerraformingInterface;

    public function getFinishDate(): int;

    public function setFinishDate(int $finishDate): ColonyTerraformingInterface;

    public function getTerraforming(): TerraformingInterface;

    public function setTerraforming(TerraformingInterface $terraforming): ColonyTerraformingInterface;

    public function getField(): PlanetFieldInterface;

    public function setField(PlanetFieldInterface $planetField): ColonyTerraformingInterface;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ColonyTerraformingInterface;

    public function getProgress(): int;
}