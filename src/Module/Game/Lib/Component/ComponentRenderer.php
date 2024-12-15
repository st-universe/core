<?php

namespace Stu\Module\Game\Lib\Component;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\Fragments\RenderFragmentInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;

class ComponentRenderer implements ComponentRendererInterface
{
    public function __construct(
        private TwigPageInterface $twigPage
    ) {}

    #[Override]
    public function renderComponent(
        RenderFragmentInterface $renderFragment,
        UserInterface $user,
        GameControllerInterface $game
    ): void {
        $renderFragment->render($user, $this->twigPage, $game);
    }
}
