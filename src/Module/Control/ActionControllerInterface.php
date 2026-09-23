<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface ActionControllerInterface extends ControllerInterface
{
    public function handle(GameControllerInterface $game): void;

    public function performSessionCheck(): bool;
}
