<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\ShowNewNote;

use NotesData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowNewNote implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_NEW_NOTE';

    public function handle(GameControllerInterface $game): void
    {
        $note = new NotesData();
        $note->setTitle('Neue Notiz');

        $game->setPageTitle("Notiz: " . $note->getTitleDecoded());
        $game->appendNavigationPart(
            sprintf(
                'notes.php?%s=1',
                static::VIEW_IDENTIFIER
            ),
            $note->getTitleDecoded()
        );
        $game->showMacro('html/notes.xhtml/note');
        $game->setTemplateVar('NOTE', $note);
    }
}
