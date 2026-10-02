<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\SaveNote;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\NoteRepositoryInterface;

final class SaveNote implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SAVE_NOTE';

    public function __construct(private SaveNoteRequestInterface $saveNoteRequest, private NoteRepositoryInterface $noteRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $userId = $context->getUser()->getId();
        $noteId = $this->saveNoteRequest->getNoteId();

        if ($noteId === 0) {
            $note = $this->noteRepository->prototype();
        } else {
            $note = $this->noteRepository->find($noteId);
            if ($note === null || $note->getUserId() !== $userId) {
                throw new AccessViolationException();
            }
        }

        $context->setView(GameController::DEFAULT_VIEW);

        $title = $this->saveNoteRequest->getTitle();

        if (mb_strlen($title) === 0) {
            $title = _('Neue Notiz');
        }

        if (mb_strlen($title) > 200) {
            $context->getInfo()->addInformation(_('Der Titel ist zu lang (maximal 200 Zeichen)'));
            return;
        }

        $note->setText($this->saveNoteRequest->getText());
        $note->setTitle($title);
        $note->setDate(time());
        $note->setUser($context->getUser());

        $this->noteRepository->save($note);

        $context->getInfo()->addInformation(_('Die Notiz wurde gespeichert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
