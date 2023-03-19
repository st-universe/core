<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\SaveNote;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;

final class SaveNote implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SAVE_NOTE';

    private SaveNoteRequestInterface $saveNoteRequest;

    private NoteRepositoryInterface $noteRepository;

    public function __construct(
        SaveNoteRequestInterface $saveNoteRequest,
        NoteRepositoryInterface $noteRepository
    ) {
        $this->saveNoteRequest = $saveNoteRequest;
        $this->noteRepository = $noteRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $noteId = $this->saveNoteRequest->getNoteId();

        if ($noteId === 0) {
            $note = $this->noteRepository->prototype();
        } else {
            $note = $this->noteRepository->find($noteId);
            if ((int) $note->getUserId() !== $userId) {
                throw new AccessViolation();
            }
        }

        $game->setView(GameController::DEFAULT_VIEW);

        $title = $this->saveNoteRequest->getTitle();

        if (mb_strlen($title) === 0) {
            $title = _('Neue Notiz');
        }

        if (mb_strlen($title) > 200) {
            $game->addInformation(_('Der Titel ist zu lang (maximal 200 Zeichen)'));
            return;
        }

        $note->setText($this->saveNoteRequest->getText());
        $note->setTitle($title);
        $note->setDate(time());
        $note->setUser($game->getUser());

        $this->noteRepository->save($note);

        $game->addInformation(_('Die Notiz wurde gespeichert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
