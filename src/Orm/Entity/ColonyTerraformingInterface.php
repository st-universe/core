<?php

namespace Stu\Orm\Entity;

use ColfieldData;
use ColonyData;

interface ColonyTerraformingInterface
{
    public function getId(): int;

    public function getColonyId(): int;

    public function setColonyId(int $colonyId): ColonyTerraformingInterface;

    public function getFieldId(): int;

    public function setFieldId(int $fieldId): ColonyTerraformingInterface;

    public function getTerraformingId(): int;

    public function setTerraformingId(int $terraformingId): ColonyTerraformingInterface;

    public function getFinishDate(): int;

    public function setFinishDate(int $finishDate): ColonyTerraformingInterface;

    public function getTerraforming(): TerraformingInterface;

    public function setTerraforming(TerraformingInterface $terraforming): ColonyTerraformingInterface;

    public function getField(): ColfieldData;

    public function getColony(): ColonyData;

    public function getProgress(): int;
}