<?php

namespace Stu\Module\Control;

interface ControllerInterface
{
    public function handle(GameControllerInterface $game): void;
}
