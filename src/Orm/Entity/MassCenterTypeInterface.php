<?php

namespace Stu\Orm\Entity;

interface MassCenterTypeInterface
{
    public function getId(): int;

    public function getDescription(): string;

    public function getSize(): int;

    public function getFirstFieldType(): MapFieldTypeInterface;
}
