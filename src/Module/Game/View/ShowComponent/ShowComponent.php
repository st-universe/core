<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowComponent;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;

final class ShowComponent implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COMPONENT';

    public function __construct(private ComponentLoaderInterface $componentLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $component = ComponentEnum::from(request::getStringFatal('component'));

        $this->componentLoader->registerComponent($component);
        $game->showMacro($component->getTemplate());
    }
}
