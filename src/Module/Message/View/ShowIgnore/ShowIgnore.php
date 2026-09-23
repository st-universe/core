<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowIgnore;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;

final class ShowIgnore implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_IGNORE';

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->showMacro('html/communication/ignoretext.twig');
    }
}
