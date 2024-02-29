<?php

namespace Stu\Orm\Entity;

interface PirateNamesInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getCount(): int;

    public function setCount(int $count): void;
}
