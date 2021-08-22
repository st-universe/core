<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RenameBuildplan;

interface RenameBuildplanRequestInterface
{
    public function getId(): int;

    public function getNewName(): string;
}
