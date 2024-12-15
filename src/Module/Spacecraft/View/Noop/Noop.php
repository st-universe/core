<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\Noop;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Noop implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'NOOP';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/noop.twig');
    }
}
