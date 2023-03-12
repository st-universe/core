<?php

namespace Stu\Module\Control;

interface ViewControllerInterface
{

    public function handle(GameControllerInterface $game): void;
}