<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\ShowNote;

use AccessViolation;
use Notes;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

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
        $game->appendNavigationPart(
            sprintf(
                'notes.php?%s=1&note=%d',
                static::VIEW_IDENTIFIER,
                $note->getId()
            ),
            $note->getTitleDecoded()
        );
        $game->showMacro('html/notes.xhtml/note');
        $game->setTemplateVar('NOTE', $note);
    }
}
