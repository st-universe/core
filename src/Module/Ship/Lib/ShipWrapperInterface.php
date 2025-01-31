<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Spacecraft\System\Data\AstroLaboratorySystemData;
use Stu\Component\Spacecraft\System\Data\BussardCollectorSystemData;
use Stu\Component\Spacecraft\System\Data\TrackerSystemData;
use Stu\Component\Spacecraft\System\Data\WebEmitterSystemData;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipWrapperInterface extends SpacecraftWrapperInterface
{
    public function get(): ShipInterface;

    public function canLandOnCurrentColony(): bool;

    public function canBeRetrofitted(): bool;

    public function getTractoringSpacecraftWrapper(): ?SpacecraftWrapperInterface;

    public function getDockedToStationWrapper(): ?StationWrapperInterface;

    public function getBussardCollectorSystemData(): ?BussardCollectorSystemData;

    public function getTrackerSystemData(): ?TrackerSystemData;

    public function getWebEmitterSystemData(): ?WebEmitterSystemData;

    public function getAstroLaboratorySystemData(): ?AstroLaboratorySystemData;
}
