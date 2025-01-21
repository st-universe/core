<?php

namespace Stu\Module\Spacecraft\Lib\Destruction;

interface SpacecraftDestroyerInterface
{
    public function getUserId(): int;

    public function getName(): string;
}
