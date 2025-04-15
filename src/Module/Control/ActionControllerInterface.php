<?php

namespace Stu\Module\Control;

interface ActionControllerInterface extends ControllerInterface
{
    public function performSessionCheck(): bool;
}
