<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\ShowNote;

use AccessViolation;
use Notes;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Tuple;

final class ShowNote implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_NOTE';

    private $showNoteRequest;

    public function __construct(
        ShowNoteRequestInterface $showNoteRequest
    ) {
        $this->showNoteRequest = $showNoteRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $note = new Notes($this->showNoteRequest->getNoteId());
        if ($note->getUserId() != $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle("Notiz: " . $note->getTitleDecoded());
        $game->addNavigationPart(
            new Tuple("notes.php?SHOW_NOTE=1&note=" . $note->getId(), $note->getTitleDecoded())
        );
        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/notes.xhtml/note');
        $game->setTemplateVar('NOTE', $note);
    }
}
