<?php

namespace Stu\Orm\Entity;

interface NamesInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getCount(): ?int;

    public function setCount(int $count): void;

    public function getType(): int;
}
