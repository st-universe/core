<?php

namespace Stu\Orm\Entity;

use Stu\Component\Game\NameTypeEnum;

interface NamesInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getCount(): ?int;

    public function setCount(int $count): void;

    public function getType(): NameTypeEnum;
}
