<?php

namespace Stu\Control;

interface ViewControllerInterface
{

    public function handle(GameController $game): void;
}