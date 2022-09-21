<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Ticks;

use Stu\Module\Control\FoobarInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowTicks implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TICKS';

    private $entityManager;

    public function __construct(FoobarInterface $foobar)
    {
        $this->entityManager = $foobar;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/admin/ticks.xhtml');
        $game->appendNavigationPart('/admin/?SHOW_TICKS=1', _('Ticks'));
        $game->setPageTitle(_('Ticks'));
    }
}
