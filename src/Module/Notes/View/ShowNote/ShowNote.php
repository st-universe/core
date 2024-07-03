<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\ShowNote;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;

final class ShowNote implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NOTE';

    public function __construct(private ShowNoteRequestInterface $showNoteRequest, private NoteRepositoryInterface $noteRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $note = $this->noteRepository->find($this->showNoteRequest->getNoteId());
        if ($note->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(sprintf(_('Notiz: %s'), $note->getTitle()));

        $game->appendNavigationPart(
            'notes.php',
            _('Notizen')
        );
        $game->appendNavigationPart(
            sprintf(
                'notes.php?%s=1&note=%d',
                static::VIEW_IDENTIFIER,
                $note->getId()
            ),
            $note->getTitle()
        );
        $game->showMacro('html/notes.xhtml/note');
        $game->setTemplateVar('NOTE', $note);
        $game->setTemplateVar('IS_NEW', false);
    }
}
