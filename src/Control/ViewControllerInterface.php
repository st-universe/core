<?php

namespace Stu\Control;

interface ViewControllerInterface
{

    public function handle(GameControllerInterface $game): void;
}