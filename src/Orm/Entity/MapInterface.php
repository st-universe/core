<?php

namespace Stu\Orm\Entity;

interface MapInterface extends LocationInterface
{
    public function getSystemsId(): ?int;

    public function setSystemsId(?int $systems_id): MapInterface;

    public function getSystemTypeId(): ?int;

    public function setSystemTypeId(?int $system_type_id): MapInterface;

    public function getInfluenceAreaId(): ?int;

    public function setInfluenceAreaId(?int $influenceAreaId): MapInterface;

    public function getBordertypeId(): ?int;

    public function getBorderColor(): string;

    public function setBordertypeId(?int $bordertype_id): MapInterface;

    public function getRegionId(): ?int;

    public function setRegionId(?int $region_id): MapInterface;

    public function getAdminRegionId(): ?int;

    public function setAdminRegionId(?int $admin_region_id): MapInterface;

    public function getSystem(): ?StarSystemInterface;

    public function setSystem(StarSystemInterface $starSystem): MapInterface;

    public function getInfluenceArea(): ?StarSystemInterface;

    public function setInfluenceArea(?StarSystemInterface $influenceArea): MapInterface;

    public function getFieldType(): MapFieldTypeInterface;

    public function getStarSystemType(): ?StarSystemTypeInterface;

    public function getMapBorderType(): ?MapBorderTypeInterface;

    public function getMapRegion(): ?MapRegionInterface;

    public function getAdministratedRegion(): ?MapRegionInterface;
}
