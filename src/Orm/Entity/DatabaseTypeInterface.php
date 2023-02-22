<?php

namespace Stu\Orm\Entity;

interface DatabaseTypeInterface
{
    public function getId(): int;

    public function setDescription(string $description): DatabaseTypeInterface;

    public function getDescription(): string;

    public function setMacro(string $macro): DatabaseTypeInterface;

    public function getMacro(): string;
}
