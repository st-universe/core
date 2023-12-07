<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\MassMail;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class MassMail implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MASS_MAIL';

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                '/admin/?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Massen-Mails')
        );
        $game->setTemplateFile('html/admin/massMail.twig');
        $game->setPageTitle(_('Massen-Mails'));
    }
}
