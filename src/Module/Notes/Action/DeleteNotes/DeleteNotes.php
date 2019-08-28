<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\DeleteNotes;

use AccessViolation;
use Notes;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeleteNotes implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DELETE_NOTES';

    private $deleteNotesRequest;

    public function __construct(
        DeleteNotesRequestInterface $deleteNotesRequest
    ) {
        $this->deleteNotesRequest = $deleteNotesRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        foreach ($this->deleteNotesRequest->getNoteIds() as $noteId) {
            $obj = new Notes($noteId);
            if (!$obj) {
                continue;
            }
            if ((int) $obj->getUserId() !== $game->getUser()->getId()) {
                throw new AccessViolation();
            }
            $obj->deleteFromDatabase();
        }

        $game->addInformation(_('Die ausgewählten Notizen wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
