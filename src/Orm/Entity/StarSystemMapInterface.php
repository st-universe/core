<?php

namespace Stu\Orm\Entity;

interface StarSystemMapInterface
{
    public function getId(): int;

    public function getSx(): int;

    public function setSx($sx): StarSystemMapInterface;

    public function getSy(): int;

    public function setSy($sy): StarSystemMapInterface;

    public function getSystemId(): int;

    public function setSystemId(int $systemId): StarSystemMapInterface;

    public function getFieldId(): int;

    public function setFieldId(int $fieldId): StarSystemMapInterface;

    public function getFieldType(): MapFieldTypeInterface;

    public function getMapRegion(): ?MapRegionInterface;

    public function getFieldStyle(): string;
}