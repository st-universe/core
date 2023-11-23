<?php

namespace Stu\Module\Game\Lib\Component;

use Stu\Module\Control\GameControllerInterface;

interface ViewComponentProviderInterface
{
    public function setTemplateVariables(GameControllerInterface $game): void;
}
