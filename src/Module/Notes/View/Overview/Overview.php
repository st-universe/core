<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\Overview;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    public function __construct(private NoteRepositoryInterface $noteRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            'notes.php',
            _('Notizen')
        );
        $game->setPageTitle(_('/ Notizen'));
        $game->setTemplateFile('html/notes.xhtml');
        $game->setTemplateVar(
            'NOTE_LIST',
            $this->noteRepository->getByUserId($game->getUser()->getId())
        );
    }
}
