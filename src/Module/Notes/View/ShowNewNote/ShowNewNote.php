<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\ShowNewNote;

use NotesData;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Tuple;

final class ShowNewNote implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_NEW_NOTE';

    public function __construct(
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $note = new NotesData();
        $note->setTitle('Neue Notiz');

        $game->setPageTitle("Notiz: " . $note->getTitleDecoded());
        $game->addNavigationPart(
            new Tuple("notes.php?SHOW_NEW_NOTE=1", $note->getTitleDecoded())
        );
        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/notes.xhtml/note');
        $game->setTemplateVar('NOTE', $note);
    }
}
