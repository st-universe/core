<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\DeleteNotes;

use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\NoteInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;

final class DeleteNotes implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_NOTES';


    private NoteRepositoryInterface $noteRepository;

    public function __construct(
        NoteRepositoryInterface $noteRepository
    ) {
        $this->noteRepository = $noteRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $delnotesString = request::indString('delnotes');

        if ($delnotesString === false) {
            return;
        }
        $delnotes = explode(',', $delnotesString);

        foreach ($delnotes as $noteId) {
            if ($noteId === "") {
                continue;
            }

            /** @var NoteInterface $obj */
            $obj = $this->noteRepository->find((int)$noteId);
            if ($obj === null) {
                continue;
            }
            if ($obj->getUserId() !== $game->getUser()->getId()) {
                throw new AccessViolation();
            }
            $this->noteRepository->delete($obj);
        }

        $game->addInformation(_('Die ausgewählten Notizen wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
