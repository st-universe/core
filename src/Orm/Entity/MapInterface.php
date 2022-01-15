<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface MapInterface
{
    public function getId(): int;

    public function getCx(): int;

    public function setCx(int $cx): MapInterface;

    public function getCy(): int;

    public function setCy(int $cy): MapInterface;

    public function getFieldId(): int;

    public function setFieldId(int $fieldId): MapInterface;

    public function getSystemsId(): ?int;

    public function setSystemsId(?int $systems_id): MapInterface;

    public function getInfluenceAreaId(): ?int;

    public function setInfluenceAreaId(?int $influenceAreaId): MapInterface;

    public function getBordertypeId(): ?int;

    public function setBordertypeId(?int $bordertype_id): MapInterface;

    public function getRegionId(): int;

    public function setRegionId(?int $region_id): MapInterface;

    public function getSystem(): ?StarSystemInterface;

    public function getInfluenceArea(): ?StarSystemInterface;

    public function setInfluenceArea(?StarSystemInterface $influenceArea): MapInterface;

    public function getFieldType(): MapFieldTypeInterface;

    public function getMapBorderType(): ?MapBorderTypeInterface;

    public function getMapRegion(): ?MapRegionInterface;

    public function getAdministratedRegion(): ?MapRegionInterface;

    public function getShips(): Collection;

    public function getSignatures(): Collection;
}
