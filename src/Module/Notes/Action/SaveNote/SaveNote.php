<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\SaveNote;

use AccessViolation;
use Notes;
use NotesData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameController;
use Stu\Control\GameControllerInterface;

final class SaveNote implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_SAVE_NOTE';

    private $saveNoteRequest;

    public function __construct(
        SaveNoteRequestInterface $saveNoteRequest
    ) {
        $this->saveNoteRequest = $saveNoteRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $noteId = $this->saveNoteRequest->getNoteId();

        if ($noteId === 0) {
            $note = new NotesData();
        } else {
            $note = new Notes($noteId);
            if ((int) $note->getUserId() !== $userId) {
                throw new AccessViolation();
            }
        }

        $note->setText($this->saveNoteRequest->getText());
        $note->setTitle($this->saveNoteRequest->getTitle());
        $note->setDate(time());
        $note->setUserId($userId);
        $note->save();

        $game->addInformation(_('Die Notiz wurde gespeichert'));

        $game->setView(GameController::DEFAULT_VIEW);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
