<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\RenameFleet;

interface RenameFleetRequestInterface
{
    public function getFleetId(): int;

    public function getNewName(): string;
}
