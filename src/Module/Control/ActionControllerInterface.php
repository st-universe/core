<?php

namespace Stu\Module\Control;

interface ActionControllerInterface
{

    public function handle(GameControllerInterface $game): void;

    public function performSessionCheck(): bool;
}