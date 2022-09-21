<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Ticks;

use Stu\Module\Control\EntityManagerLoggingInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowTicks implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TICKS';

    private EntityManagerLoggingInterface $entityManagerLogging;

    public function __construct(EntityManagerLoggingInterface $entityManagerLogging)
    {
        $this->entityManagerLogging = $entityManagerLogging;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/admin/ticks.xhtml');
        $game->appendNavigationPart('/admin/?SHOW_TICKS=1', _('Ticks'));
        $game->setPageTitle(_('Ticks'));
    }
}
