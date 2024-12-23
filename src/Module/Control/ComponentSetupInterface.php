<?php

namespace Stu\Module\Control;

interface ComponentSetupInterface
{

    public function setup(GameControllerInterface $game): void;
}
