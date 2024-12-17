<?php

namespace Stu\Lib\Component;

use Override;
use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;

class ComponentRenderer implements ComponentRendererInterface
{
    #[Override]
    public function renderComponent(
        ComponentInterface $component,
        GameControllerInterface $game
    ): void {
        $component->setTemplateVariables($game);
    }
}
