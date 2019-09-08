<?php

namespace Stu\Orm\Entity;

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

    public function getBordertypeId(): ?int;

    public function setBordertypeId(?int $bordertype_id): MapInterface;

    public function getRegionId(): int;

    public function setRegionId(?int $region_id): MapInterface;

    public function getSystem(): ?StarSystemInterface;

    public function getFieldType(): MapFieldTypeInterface;

    public function getMapBorderType(): ?MapBorderTypeInterface;

    public function getMapRegion(): ?MapRegionInterface;
}