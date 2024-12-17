<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowComponent;

use Override;
use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowComponent implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COMPONENT';

    public function __construct(private ComponentRegistrationInterface $componentRegistration) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $exploded = explode('_', request::getStringFatal('id'), 2);
        $componentEnum = ModuleViewEnum::from(strtolower($exploded[0]))->getComponentEnum($exploded[1]);

        $this->componentRegistration->registerComponent($componentEnum);
        $game->showMacro($componentEnum->getTemplate());
    }
}
