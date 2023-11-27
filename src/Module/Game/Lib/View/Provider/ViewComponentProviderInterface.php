<?php

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Module\Control\GameControllerInterface;

interface ViewComponentProviderInterface
{
    public function setTemplateVariables(GameControllerInterface $game): void;
}
