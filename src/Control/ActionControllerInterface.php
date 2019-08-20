<?php

namespace Stu\Control;

interface ActionControllerInterface
{

    public function handle(GameControllerInterface $game): void;

    public function performSessionCheck(): bool;
}