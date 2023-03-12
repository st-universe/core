<?php

namespace Stu\Orm\Entity;

interface PlanetFieldTypeInterface
{
    public function getId(): int;

    public function getFieldType(): int;

    public function setFieldType(int $fieldType): PlanetFieldTypeInterface;

    public function getDescription(): string;

    public function setDescription(string $description): PlanetFieldTypeInterface;

    public function getBaseFieldType(): int;

    public function setBaseFieldType(int $baseFieldType): PlanetFieldTypeInterface;

    public function getCategory(): int;
}
