<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\MassMail;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class MassMail implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MASS_MAIL';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                '/admin/?%s=1',
                self::VIEW_IDENTIFIER
            ),
            _('Massen-Mails')
        );
        $game->setTemplateFile('html/admin/massMail.twig');
        $game->setPageTitle(_('Massen-Mails'));
    }
}
