<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface ControllerInterface
{
    public function handle(GameControllerInterface $game): void;
}
