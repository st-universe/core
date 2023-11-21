<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;

interface GuiComponentProviderInterface
{
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void;
}
