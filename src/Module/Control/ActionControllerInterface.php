<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface ActionControllerInterface extends ControllerInterface
{
    public function performSessionCheck(): bool;
}
