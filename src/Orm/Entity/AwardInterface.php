<?php

namespace Stu\Orm\Entity;

interface AwardInterface
{
    public function getId(): int;

    public function getPrestige(): int;

    public function getDescription(): string;
}
