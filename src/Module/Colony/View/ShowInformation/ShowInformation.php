<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowInformation;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowInformation implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_INFORMATION';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/systeminformationAndJsBeforeAfterRender.twig');
    }
}
